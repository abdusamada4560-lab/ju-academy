<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'role_in_project',
        'assigned_date',
        'removed_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'removed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const ROLE_DEVELOPER = 'Developer';
    const ROLE_TEAM_LEAD = 'Team Lead';

    /**
     * Get the project this member belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who is a member
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if member is active (not removed)
     */
    public function isActive(): bool
    {
        return is_null($this->removed_date);
    }

    /**
     * Remove member from project
     */
    public function removeMember(): void
    {
        $this->update(['removed_date' => now()->toDateString()]);
    }
}
