<?php

namespace App\Support;

use HasinHayder\Tyro\Models\AuditLog;

class AuditLogSummary
{
    public static function for(AuditLog $log): string
    {
        $new = $log->new_values ?? [];
        $old = $log->old_values ?? [];

        return match ($log->event) {
            'user.created' => 'Created user "'.self::resolveEmail($new, $log).'"',
            'user.updated' => 'Updated user "'.self::resolveEmail($new, $log).'"',
            'user.deleted' => 'Deleted user "'.self::resolveEmail($old, $log).'"',
            default => $log->summary,
        };
    }

    private static function resolveEmail(array $values, AuditLog $log): string
    {
        if (! empty($values['email'])) {
            return $values['email'];
        }

        return $log->user?->email ?? "user #{$log->auditable_id}";
    }
}
