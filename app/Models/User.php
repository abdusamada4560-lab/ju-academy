<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'role_id',
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

    /**
     * Get the role of this user (Business Rule 2: one role per user)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
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
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role->role_name === $roleName;
    }

    /**
     * Check if user is a director (Business Rule - strategic oversight)
     */
    public function isDirector(): bool
    {
        return $this->role->isDirector();
    }

    /**
     * Check if user is a team leader (Business Rule - can create projects)
     */
    public function isTeamLeader(): bool
    {
        return $this->role->isTeamLeader();
    }

    /**
     * Check if user is a project leader (Business Rule - manages one project)
     */
    public function isProjectLeader(): bool
    {
        return $this->role->isProjectLeader();
    }

    /**
     * Check if user is a developer (Business Rule - completes tasks)
     */
    public function isDeveloper(): bool
    {
        return $this->role->isDeveloper();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }
}
