<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // ✅ ADD THIS

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // ✅ ADD HasRoles TRAIT

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // NOTE: We NO LONGER use role_id foreign key
    // Spatie manages roles through the model_has_roles pivot table

    /**
     * Get the primary role of this user (convenience method)
     * Returns the first role assigned to the user
     */
    public function primaryRole()
    {
        return $this->roles()->first();
    }

    /**
     * Get projects created by this user
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * Get projects led by this user
     */
    public function ledProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_leader_id');
    }

    /**
     * Get projects this user is a member of (Business Rule 5: developers may belong to multiple projects)
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_members',
            'user_id',
            'project_id'
        )->withPivot('role_in_project', 'assigned_date', 'removed_date')
         ->withTimestamps();
    }

    /**
     * Get work packages assigned to this user (Business Rule 7: every task has one assigned developer)
     */
    public function assignedWorkPackages(): HasMany
    {
        return $this->hasMany(WorkPackage::class, 'assigned_to');
    }

    /**
     * Get work packages created by this user
     */
    public function createdWorkPackages(): HasMany
    {
        return $this->hasMany(WorkPackage::class, 'created_by');
    }

    /**
     * Get documents uploaded by this user
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Get comments authored by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    /**
     * Get activity log entries for this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    /**
     * Get notifications sent to this user
     */
    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    /**
     * Get notifications sent by this user
     */
    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }

    /**
     * Get audit log entries for this user
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Get system settings updated by this user
     */
    public function updatedSettings(): HasMany
    {
        return $this->hasMany(SystemSetting::class, 'updated_by');
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * ============================================================================
     * SPATIE PERMISSION CONVENIENCE METHODS
     * ============================================================================
     * These methods provide shortcuts for common role checks
     * They use Spatie's built-in hasRole() and can() methods
     */

    /**
     * Check if user is an ICT Director
     * Spatie method: hasRole() checks model_has_roles table
     */
    public function isDirector(): bool
    {
        return $this->hasRole('ICT Director');
    }

    /**
     * Check if user is a Development Team Leader
     */
    public function isTeamLeader(): bool
    {
        return $this->hasRole('Development Team Leader');
    }

    /**
     * Check if user is a Project Leader
     */
    public function isProjectLeader(): bool
    {
        return $this->hasRole('Project Leader');
    }

    /**
     * Check if user is a Developer
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole('Developer');
    }

    /**
     * Check if user has multiple roles
     */
    public function hasMultipleRoles(): bool
    {
        return $this->roles()->count() > 1;
    }

    /**
     * Get all role names as array
     */
    public function getRoleNamesArray(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * ============================================================================
     * BUSINESS RULE ENFORCEMENT
     * ============================================================================
     */

    /**
     * Business Rule 3: Only Development Team Leader can create projects
     */
    public function canCreateProject(): bool
    {
        return $this->hasPermissionTo('create_project') || $this->isTeamLeader();
    }

    /**
     * Business Rule 4: Project Leader can assign tasks
     */
    public function canAssignTasks(): bool
    {
        return $this->hasPermissionTo('assign_task') || $this->isProjectLeader() || $this->isTeamLeader();
    }

    /**
     * Business Rule 7: Only assigned developer can update their own tasks
     */
    public function canUpdateWorkPackage(WorkPackage $workPackage): bool
    {
        // Developer can only update their own tasks
        if ($this->isDeveloper()) {
            return $this->id === $workPackage->assigned_to;
        }
        // Project Leader, Team Leader, Director can update any task in their project
        return $this->isProjectLeader() || $this->isTeamLeader() || $this->isDirector();
    }

    /**
     * Business Rule 10: Only Team Leader can reopen closed projects
     */
    public function canReopenProject(Project $project): bool
    {
        return $project->isClosed() && ($this->isTeamLeader() || $this->isDirector());
    }

    /**
     * Business Rule 11: Only project members can access project documents
     */
    public function canAccessProjectDocument(Project $project): bool
    {
        // Director and Team Leader can access any document
        if ($this->isDirector() || $this->isTeamLeader()) {
            return true;
        }
        // Other users must be project members
        return $project->members()->where('users.id', $this->id)->exists();
    }

    /**
     * Business Rule 12: Only Director and Team Leader can view audit logs
     */
    public function canViewAuditLog(): bool
    {
        return $this->isDirector() || $this->isTeamLeader();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }
}
