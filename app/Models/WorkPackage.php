<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WorkPackage extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'type',
        'status',
        'assigned_to',
        'created_by',
        'parent_id',
        'priority',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'due_date',
        'start_date',
        'completed_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'completed_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const TYPE_TASK = 'task';
    const TYPE_MILESTONE = 'milestone';
    const TYPE_ISSUE = 'issue';

    const STATUS_TODO = 'To Do';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_IN_REVIEW = 'In Review';
    const STATUS_DONE = 'Done';
    const STATUS_BLOCKED = 'Blocked';

    const PRIORITY_LOW = 'Low';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_CRITICAL = 'Critical';

    /**
     * Get the project this work package belongs to (Business Rule 6)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the developer this work package is assigned to (Business Rule 7)
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this work package
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get parent work package (for sub-tasks)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'parent_id');
    }

    /**
     * Get child work packages (sub-tasks)
     */
    public function children(): HasMany
    {
        return $this->hasMany(WorkPackage::class, 'parent_id');
    }

    /**
     * Get related work packages (dependencies)
     */
    public function relations(): HasMany
    {
        return $this->hasMany(WorkPackageRelation::class, 'source_work_package_id');
    }

    /**
     * Get reverse relations (work packages that depend on this one)
     */
    public function reverseRelations(): HasMany
    {
        return $this->hasMany(WorkPackageRelation::class, 'target_work_package_id');
    }

    /**
     * Get documents for this work package
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'work_package_id');
    }

    /**
     * Get comments for this work package
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'work_package_id');
    }

    /**
     * Get activity log for this work package
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'work_package_id');
    }

    /**
     * Get notifications for this work package
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'work_package_id');
    }

    /**
     * Check if work package is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now()->toDateString() && $this->status !== self::STATUS_DONE;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_DONE,
            'progress_percentage' => 100,
            'completed_date' => now()->toDateString(),
        ]);
        $this->project->updateProgress();
    }

    /**
     * Mark as blocked
     */
    public function markAsBlocked(): void
    {
        $this->update(['status' => self::STATUS_BLOCKED]);
    }

    /**
     * Only the assigned developer can update this work package (Business Rule 8)
     */
    public function canBeUpdatedBy(User $user): bool
    {
        return $this->assigned_to === $user->id || $user->isProjectLeader() || $user->isTeamLeader() || $user->isDirector();
    }
}
