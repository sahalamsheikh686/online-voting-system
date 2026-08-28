<?php

namespace App\Support\Reports;

use Illuminate\Http\Response;

class ExcelReportExporter
{
    public function download(array $payload, string $filename): Response
    {
        return response($this->render($payload), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xls"',
        ]);
    }

    public function render(array $payload): string
    {
        $columnCount = $this->resolveColumnCount($payload);
        $html = [];
        $html[] = '<html><head><meta charset="UTF-8"><style>';
        $html[] = 'body{font-family:Segoe UI,Arial,sans-serif;color:#1e293b;background:#ffffff;}';
        $html[] = 'table{border-collapse:collapse;width:100%;margin:0 0 18px;table-layout:auto;}';
        $html[] = 'th,td{border:1px solid #cbd5e1;padding:8px 10px;vertical-align:top;white-space:normal;word-break:break-word;}';
        $html[] = '.report-header td{border-color:#1d4ed8;}';
        $html[] = '.report-title{background:#eff6ff;color:#0f172a;font-size:22px;font-weight:700;padding:14px 12px;}';
        $html[] = '.meta-label{background:#f8fafc;color:#475569;font-weight:600;width:180px;}';
        $html[] = '.meta-value{color:#0f172a;}';
        $html[] = '.section-title{background:#1d4ed8;color:#ffffff;font-size:16px;font-weight:700;padding:10px 12px;}';
        $html[] = '.section-note{background:#f8fafc;color:#475569;font-style:italic;}';
        $html[] = '.summary-label{background:#f8fafc;font-weight:600;width:240px;}';
        $html[] = '.table-head th{background:#dbeafe;color:#0f172a;font-weight:700;text-align:left;}';
        $html[] = '.report-table tbody tr:nth-child(even) td{background:#f8fbff;}';
        $html[] = '.notes-row td{background:#fff7ed;}';
        $html[] = '</style></head><body>';
        $html[] = '<table class="report-header">';
        $html[] = '<tr><td colspan="'.$columnCount.'" class="report-title">'.$this->escape($payload['title'] ?? 'Report').'</td></tr>';
        $html[] = '<tr><td class="meta-label">Context</td><td colspan="'.max($columnCount - 1, 1).'" class="meta-value">'.$this->escape($payload['context'] ?? '-').'</td></tr>';
        $html[] = '<tr><td class="meta-label">Generated At</td><td colspan="'.max($columnCount - 1, 1).'" class="meta-value">'.$this->escape($payload['generated_at'] ?? '-').'</td></tr>';
        $html[] = '</table>';

        if (! empty($payload['summary'])) {
            $html[] = '<table class="summary">';
            $html[] = '<tr><td colspan="2" class="section-title">Summary</td></tr>';
            foreach ($payload['summary'] as $item) {
                $html[] = '<tr><td class="summary-label">'.$this->escape($item['label'] ?? '').'</td><td>'.$this->escape($item['value'] ?? '').'</td></tr>';
            }
            $html[] = '</table>';
        }

        foreach ($payload['sections'] ?? [] as $section) {
            $headers = $section['headers'] ?? [];
            $rows = $section['rows'] ?? [];
            $sectionColumnCount = max(count($headers), 1);

            $html[] = '<table class="report-table">';
            $html[] = '<tr><td colspan="'.$sectionColumnCount.'" class="section-title">'.$this->escape($section['title'] ?? 'Section').'</td></tr>';
            if (! empty($section['description'])) {
                $html[] = '<tr><td colspan="'.$sectionColumnCount.'" class="section-note">'.$this->escape($section['description']).'</td></tr>';
            }
            $html[] = '<thead class="table-head"><tr>';
            foreach ($headers as $header) {
                $html[] = '<th>'.$this->escape($header).'</th>';
            }
            $html[] = '</tr></thead><tbody>';

            if (empty($rows)) {
                $html[] = '<tr><td colspan="'.$sectionColumnCount.'">No data available.</td></tr>';
            } else {
                foreach ($rows as $row) {
                    $html[] = '<tr>';
                    foreach ($row as $cell) {
                        $html[] = '<td>'.$this->escape((string) $cell).'</td>';
                    }
                    $html[] = '</tr>';
                }
            }

            $html[] = '</tbody></table>';
        }

        if (! empty($payload['notes'])) {
            $html[] = '<table>';
            $html[] = '<tr><td class="section-title">Notes</td></tr>';
            foreach ($payload['notes'] as $note) {
                $html[] = '<tr class="notes-row"><td>'.$this->escape($note).'</td></tr>';
            }
            $html[] = '</table>';
        }

        $html[] = '</body></html>';

        return implode('', $html);
    }

    private function resolveColumnCount(array $payload): int
    {
        $maxColumns = 2;

        foreach ($payload['sections'] ?? [] as $section) {
            $maxColumns = max($maxColumns, count($section['headers'] ?? []));
        }

        return $maxColumns;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
