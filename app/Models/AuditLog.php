<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_log';

    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'description',
        'severity',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const SEVERITY_INFO = 'Info';
    const SEVERITY_WARNING = 'Warning';
    const SEVERITY_CRITICAL = 'Critical';

    const ACTION_PROJECT_CREATED = 'project_created';
    const ACTION_PROJECT_CLOSED = 'project_closed';
    const ACTION_USER_CREATED = 'user_created';
    const ACTION_ROLE_CHANGED = 'role_changed';
    const ACTION_MEMBER_REASSIGNED = 'member_reassigned';
    const ACTION_SETTINGS_CHANGED = 'settings_changed';

    /**
     * Get the user who performed this action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Visible only to ICT Director and Team Leader (Business Rule 12)
     */
    public static function scopeVisibleToUser($query, User $user)
    {
        if (!$user->isDirector() && !$user->isTeamLeader()) {
            return $query->whereRaw('1 = 0'); // Hide from Project Leaders and Developers
        }
        return $query;
    }
}
