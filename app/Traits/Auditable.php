<?php

namespace App\Traits;

use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait Auditable
{
    /**
     * Boot the Auditable trait for the model.
     */
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            if ($model instanceof \App\Models\User) {
                return; // User registration/creation is handled manually to prevent duplicates
            }
            static::logEvent($model, 'created', null, static::filterSensitive($model, $model->toArray()));
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            
            // Only log if there are actual changes
            if (empty($changes)) {
                return;
            }

            if ($model instanceof \App\Models\User) {
                // If only email is changed, Tyro already logs user.email_changed
                if (count($changes) === 1 && isset($changes['email'])) {
                    return;
                }
                
                // Ignore updating remember_token or updated_at alone
                $ignoredKeys = ['remember_token', 'updated_at'];
                $actualChanges = array_diff(array_keys($changes), $ignoredKeys);
                if (empty($actualChanges)) {
                    return;
                }
            }

            // Get original values of changed keys only
            $original = Arr::only($model->getOriginal(), array_keys($changes));

            static::logEvent(
                $model,
                'updated',
                static::filterSensitive($model, $original),
                static::filterSensitive($model, $changes)
            );
        });

        static::deleted(function (Model $model) {
            if ($model instanceof \App\Models\User) {
                return; // User deletion is handled manually by Tyro
            }
            static::logEvent($model, 'deleted', static::filterSensitive($model, $model->toArray()), null);
        });
    }

    /**
     * Log the audit event using TyroAudit.
     */
    protected static function logEvent(Model $model, string $action, ?array $oldValues, ?array $newValues)
    {
        $modelName = strtolower(class_basename($model));
        $event = "{$modelName}.{$action}";

        TyroAudit::log($event, $model, $oldValues, $newValues);
    }

    /**
     * Filter out sensitive attributes from the logged data.
     */
    protected static function filterSensitive(Model $model, array $data): array
    {
        $hidden = method_exists($model, 'getHidden') ? $model->getHidden() : [];
        $sensitiveKeys = ['password', 'password_confirmation', 'remember_token', 'token', 'secret', 'key'];
        $exclude = array_unique(array_merge($hidden, $sensitiveKeys));
        
        return Arr::except($data, $exclude);
    }
}
