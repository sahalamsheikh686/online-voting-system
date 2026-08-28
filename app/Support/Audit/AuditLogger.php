<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLogger
{
    public static function record(
        string $action,
        ?User $actor = null,
        ?string $description = null,
        ?int $electionId = null,
        array $metadata = []
    ): void {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $request = request();
        $actor ??= auth()->user();

        try {
            AuditLog::query()->create([
                'user_id' => $actor?->id,
                'election_id' => $electionId,
                'action' => $action,
                'description' => $description ?: str($action)->replace('_', ' ')->title()->toString(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'metadata' => $metadata,
                'logged_at' => now(),
            ]);
        } catch (Throwable) {
            // Keep the main user action working even if audit logging is temporarily unavailable.
        }
    }
}
