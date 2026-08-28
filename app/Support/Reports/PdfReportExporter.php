<?php

namespace App\Support\Reports;

use Illuminate\Http\Response;

class PdfReportExporter
{
    private const PAGE_WIDTH = 612;

    private const PAGE_HEIGHT = 792;

    private const MARGIN_X = 42;

    private const MARGIN_TOP = 748;

    private const MARGIN_BOTTOM = 44;

    public function download(array $payload, string $filename): Response
    {
        return response($this->render($payload), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    public function render(array $payload): string
    {
        $pages = $this->buildPages($payload);
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageIds = [];
        $fontIds = [
            'F1' => 3,
            'F2' => 4,
            'F3' => 5,
            'F4' => 6,
        ];
        $nextObjectId = 7;

        foreach ($pages as $stream) {
            $pageId = $nextObjectId++;
            $contentId = $nextObjectId++;
            $pageIds[] = $pageId;

            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::PAGE_WIDTH.' '.self::PAGE_HEIGHT.'] /Resources << /Font << /F1 '.$fontIds['F1'].' 0 R /F2 '.$fontIds['F2'].' 0 R /F3 '.$fontIds['F3'].' 0 R /F4 '.$fontIds['F4'].' 0 R >> >> /Contents '.$contentId." 0 R >>";
            $objects[$contentId] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        $kids = implode(' ', array_map(fn (int $id) => "{$id} 0 R", $pageIds));
        $objects[2] = '<< /Type /Pages /Kids [ '.$kids.' ] /Count '.count($pageIds).' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        $objects[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $objectCount = max(array_keys($objects));
        $pdf .= "xref\n0 ".($objectCount + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $objectCount; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i] ?? 0)."\n";
        }

        $pdf .= 'trailer << /Size '.($objectCount + 1).' /Root 1 0 R >>'."\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function buildPages(array $payload): array
    {
        $state = [
            'pages' => [],
            'ops' => [],
            'y' => self::MARGIN_TOP,
        ];

        $contentWidth = self::PAGE_WIDTH - (self::MARGIN_X * 2);

        $this->addBanner($state, $payload['title'] ?? 'Report', $contentWidth);
        $this->addWrappedParagraph($state, $payload['context'] ?? '-', [
            'font' => 'F1',
            'size' => 10.5,
            'line_height' => 14,
            'max_chars' => 88,
            'color' => [0.31, 0.37, 0.45],
        ]);
        $this->addWrappedParagraph($state, 'Generated at: '.($payload['generated_at'] ?? '-'), [
            'font' => 'F1',
            'size' => 10.5,
            'line_height' => 14,
            'max_chars' => 88,
            'color' => [0.31, 0.37, 0.45],
        ]);
        $this->addSpacer($state, 10);

        if (! empty($payload['summary'])) {
            $this->addSectionHeader($state, 'Summary', [0.10, 0.33, 0.78], $contentWidth);

            foreach ($payload['summary'] as $item) {
                $this->addKeyValueRow(
                    $state,
                    (string) ($item['label'] ?? ''),
                    (string) ($item['value'] ?? ''),
                    $contentWidth
                );
            }

            $this->addSpacer($state, 8);
        }

        foreach ($payload['sections'] ?? [] as $section) {
            $sectionTitle = (string) ($section['title'] ?? 'Section');
            $headers = array_map('strval', $section['headers'] ?? []);
            $rows = array_map(
                fn ($row) => array_map(fn ($value) => (string) $value, $row),
                $section['rows'] ?? []
            );

            $this->addSectionHeader($state, $sectionTitle, [0.12, 0.45, 0.85], $contentWidth);

            if (! empty($section['description'])) {
                $this->addWrappedParagraph($state, (string) $section['description'], [
                    'font' => 'F1',
                    'size' => 9.5,
                    'line_height' => 12,
                    'max_chars' => 92,
                    'color' => [0.36, 0.43, 0.52],
                ]);
                $this->addSpacer($state, 4);
            }

            if (empty($headers) && empty($rows)) {
                $this->addWrappedParagraph($state, 'No data available.', [
                    'font' => 'F1',
                    'size' => 10,
                    'line_height' => 13,
                    'max_chars' => 92,
                ]);
                $this->addSpacer($state, 10);
                continue;
            }

            $this->addTable($state, $sectionTitle, $headers, $rows, $contentWidth);
            $this->addSpacer($state, 10);
        }

        if (! empty($payload['notes'])) {
            $this->addSectionHeader($state, 'Notes', [0.84, 0.45, 0.10], $contentWidth);

            foreach ($payload['notes'] as $note) {
                $this->addWrappedParagraph($state, '- '.(string) $note, [
                    'font' => 'F1',
                    'size' => 10,
                    'line_height' => 13,
                    'max_chars' => 90,
                ]);
            }
        }

        $this->flushPage($state);

        return $state['pages'];
    }

    private function addBanner(array &$state, string $title, float $width): void
    {
        $this->ensureSpace($state, 40);
        $this->drawRect($state, self::MARGIN_X, $state['y'] - 28, $width, 30, [0.11, 0.31, 0.85]);
        $this->drawText($state, $title, self::MARGIN_X + 12, $state['y'] - 20, 'F2', 20, [1, 1, 1]);
        $state['y'] -= 40;
    }

    private function addSectionHeader(array &$state, string $title, array $color, float $width): void
    {
        $this->ensureSpace($state, 24);
        $this->drawRect($state, self::MARGIN_X, $state['y'] - 16, $width, 18, $color);
        $this->drawText($state, $title, self::MARGIN_X + 10, $state['y'] - 11.5, 'F2', 12, [1, 1, 1]);
        $state['y'] -= 24;
    }

    private function addKeyValueRow(array &$state, string $label, string $value, float $contentWidth): void
    {
        $valueLines = $this->wrapText($value, 58);
        $lineHeight = 13;
        $rowHeight = max(count($valueLines), 1) * $lineHeight + 3;

        $this->ensureSpace($state, $rowHeight);
        $this->drawLine($state, self::MARGIN_X, $state['y'] - 2, self::MARGIN_X + $contentWidth, $state['y'] - 2, [0.86, 0.90, 0.96]);
        $this->drawText($state, $label, self::MARGIN_X + 6, $state['y'] - 11, 'F2', 10.5, [0.10, 0.17, 0.27]);

        foreach ($valueLines as $index => $line) {
            $this->drawText($state, $line, self::MARGIN_X + 170, $state['y'] - 11 - ($index * $lineHeight), 'F1', 10.5, [0.12, 0.17, 0.27]);
        }

        $state['y'] -= $rowHeight;
    }

    private function addTable(array &$state, string $sectionTitle, array $headers, array $rows, float $contentWidth): void
    {
        $headers = ! empty($headers) ? $headers : ['Details'];
        $widths = $this->calculateColumnWidths($headers, $rows, 92);
        $headerLines = $this->formatTableRow($headers, $widths);

        $drawHeader = function () use (&$state, $headerLines, $contentWidth): void {
            foreach ($headerLines as $line) {
                $this->ensureSpace($state, 12);
                $this->drawRect($state, self::MARGIN_X, $state['y'] - 10, $contentWidth, 11, [0.89, 0.93, 0.99]);
                $this->drawText($state, $line, self::MARGIN_X + 4, $state['y'] - 8.5, 'F4', 8.6, [0.07, 0.12, 0.22]);
                $state['y'] -= 12;
            }
        };

        $drawHeader();

        if (empty($rows)) {
            $this->ensureSpace($state, 13);
            $this->drawText($state, 'No data available.', self::MARGIN_X + 4, $state['y'] - 9, 'F3', 9, [0.16, 0.20, 0.28]);
            $state['y'] -= 13;
            return;
        }

        foreach ($rows as $row) {
            $rowLines = $this->formatTableRow($row, $widths);
            $rowHeight = count($rowLines) * 11 + 4;

            if ($state['y'] - $rowHeight < self::MARGIN_BOTTOM) {
                $this->pushNewPage($state);
                $this->addSectionHeader($state, $sectionTitle.' (continued)', [0.12, 0.45, 0.85], $contentWidth);
                $drawHeader();
            }

            foreach ($rowLines as $line) {
                $this->ensureSpace($state, 11);
                $this->drawText($state, $line, self::MARGIN_X + 4, $state['y'] - 8.3, 'F3', 8.7, [0.14, 0.18, 0.25]);
                $state['y'] -= 11;
            }

            $this->drawLine($state, self::MARGIN_X, $state['y'] + 1, self::MARGIN_X + $contentWidth, $state['y'] + 1, [0.90, 0.93, 0.97]);
            $state['y'] -= 4;
        }
    }

    private function addWrappedParagraph(array &$state, string $text, array $options = []): void
    {
        $font = $options['font'] ?? 'F1';
        $size = (float) ($options['size'] ?? 10.5);
        $lineHeight = (float) ($options['line_height'] ?? 13);
        $maxChars = (int) ($options['max_chars'] ?? 88);
        $color = $options['color'] ?? [0.12, 0.17, 0.27];
        $x = (float) ($options['x'] ?? self::MARGIN_X);

        foreach ($this->wrapText($text, $maxChars) as $line) {
            $this->ensureSpace($state, $lineHeight);
            $this->drawText($state, $line, $x, $state['y'] - ($lineHeight - 3), $font, $size, $color);
            $state['y'] -= $lineHeight;
        }
    }

    private function addSpacer(array &$state, float $height): void
    {
        $state['y'] -= $height;
    }

    private function calculateColumnWidths(array $headers, array $rows, int $totalWidth): array
    {
        $columnCount = max(count($headers), 1);
        $weights = [];

        for ($index = 0; $index < $columnCount; $index++) {
            $weight = strlen($headers[$index] ?? '');

            foreach (array_slice($rows, 0, 25) as $row) {
                $weight = max($weight, strlen($row[$index] ?? ''));
            }

            $weights[$index] = max(min($weight, 32), 8);
        }

        $sum = array_sum($weights) ?: $columnCount;
        $allocated = [];
        $remaining = $totalWidth - (($columnCount - 1) * 3);

        foreach ($weights as $index => $weight) {
            $allocated[$index] = max((int) floor(($weight / $sum) * $remaining), 8);
        }

        $difference = $remaining - array_sum($allocated);

        while ($difference !== 0) {
            foreach ($allocated as $index => $width) {
                if ($difference === 0) {
                    break;
                }

                if ($difference > 0) {
                    $allocated[$index]++;
                    $difference--;
                    continue;
                }

                if ($width > 8) {
                    $allocated[$index]--;
                    $difference++;
                }
            }
        }

        return $allocated;
    }

    private function formatTableRow(array $row, array $widths): array
    {
        $wrappedCells = [];
        $lineCount = 1;

        foreach ($widths as $index => $width) {
            $cell = $row[$index] ?? '';
            $lines = $this->wrapText($cell, $width);
            $wrappedCells[$index] = $lines;
            $lineCount = max($lineCount, count($lines));
        }

        $lines = [];

        for ($lineIndex = 0; $lineIndex < $lineCount; $lineIndex++) {
            $segments = [];

            foreach ($widths as $index => $width) {
                $segment = $wrappedCells[$index][$lineIndex] ?? '';
                $segments[] = str_pad(substr($segment, 0, $width), $width);
            }

            $lines[] = rtrim(implode(' | ', $segments));
        }

        return $lines;
    }

    private function ensureSpace(array &$state, float $height): void
    {
        if ($state['y'] - $height < self::MARGIN_BOTTOM) {
            $this->pushNewPage($state);
        }
    }

    private function pushNewPage(array &$state): void
    {
        $this->flushPage($state);
        $state['ops'] = [];
        $state['y'] = self::MARGIN_TOP;
    }

    private function flushPage(array &$state): void
    {
        if ($state['ops'] === []) {
            return;
        }

        $state['pages'][] = implode("\n", $state['ops']);
    }

    private function drawText(array &$state, string $text, float $x, float $y, string $font, float $size, array $color): void
    {
        $escaped = $this->escapePdfText($this->toAscii($text));
        $state['ops'][] = "BT\n/{$font} {$size} Tf\n".$this->pdfColor($color)." rg\n{$x} {$y} Td\n({$escaped}) Tj\nET";
    }

    private function drawRect(array &$state, float $x, float $y, float $width, float $height, array $color): void
    {
        $state['ops'][] = "q\n".$this->pdfColor($color)." rg\n{$x} {$y} {$width} {$height} re f\nQ";
    }

    private function drawLine(array &$state, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $state['ops'][] = "q\n".$this->pdfColor($color)." RG\n0.6 w\n{$x1} {$y1} m\n{$x2} {$y2} l\nS\nQ";
    }

    private function wrapText(string $value, int $width = 88): array
    {
        $value = trim($this->toAscii($value));

        if ($value === '') {
            return [''];
        }

        return preg_split("/\r\n|\n|\r/", wordwrap($value, $width, "\n", true)) ?: [$value];
    }

    private function toAscii(string $value): string
    {
        $sanitized = preg_replace('/[^\x20-\x7E]/', '', $value);

        return $sanitized === null ? '' : $sanitized;
    }

    private function escapePdfText(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $value
        );
    }

    private function pdfColor(array $color): string
    {
        return implode(' ', array_map(
            fn ($value) => rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.'),
            $color
        ));
    }
}
