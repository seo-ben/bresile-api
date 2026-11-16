<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Record an immutable audit log entry.
     */
    public function log(
        string $eventType,
        ?Model $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $actorId = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id'    => $actorId ?? Auth::id(),
            'action'      => $eventType,
            'entity_type' => $entity ? class_basename($entity) : 'System',
            'entity_id'   => $entity?->getKey(),
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'ip_address'  => Request::ip(),
            'created_at'  => now(),
        ]);
    }
}
