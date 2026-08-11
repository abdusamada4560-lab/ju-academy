<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_name',
        'description',
        'status',
        'created_by',
        'project_leader_id',
        'start_date',
        'end_date',
        'progress_percentage',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'budget' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Project statuses
     */
    const STATUS_PLANNING = 'Planning';
    const STATUS_ACTIVE = 'Active';
    const STATUS_ON_HOLD = 'On Hold';
    const STATUS_CLOSED = 'Closed';
    const STATUS_ARCHIVED = 'Archived';

    /**
     * Get the user who created this project (Business Rule 3: only Team Leader creates)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the project leader (Business Rule 4: every project must have one Project Leader)
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_leader_id');
    }

    /**
     * Get all members of this project
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_members',
            'project_id',
            'user_id'
        )->withPivot('role_in_project', 'assigned_date', 'removed_date')
         ->withTimestamps();
    }

    /**
     * Get all work packages in this project (Business Rule 6: every task belongs to one project)
     */
    public function workPackages(): HasMany
    {
        return $this->hasMany(WorkPackage::class, 'project_id');
    }

    /**
     * Get all tasks in this project
     */
    public function tasks(): HasMany
    {
        return $this->workPackages()->where('type', 'task');
    }

    /**
     * Get all milestones in this project
     */
    public function milestones(): HasMany
    {
        return $this->workPackages()->where('type', 'milestone');
    }

    /**
     * Get all issues in this project
     */
    public function issues(): HasMany
    {
        return $this->workPackages()->where('type', 'issue');
    }

    /**
     * Get all documents in this project
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'project_id');
    }

    /**
     * Get all comments in this project
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'project_id');
    }

    /**
     * Get activity log for this project
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'project_id');
    }

    /**
     * Get notifications for this project
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'project_id');
    }

    /**
     * Calculate project progress automatically (Business Rule 9: auto progress)
     */
    public function calculateProgress(): float
    {
        $workPackages = $this->workPackages()->get();
        
        if ($workPackages->isEmpty()) {
            return 0;
        }

        $totalProgress = $workPackages->sum('progress_percentage');
        $progress = $totalProgress / $workPackages->count();

        return round($progress, 2);
    }

    /**
     * Update project progress
     */
    public function updateProgress(): void
    {
        $this->update(['progress_percentage' => $this->calculateProgress()]);
    }

    /**
     * Check if project is closed (Business Rule 10: closed projects immutable unless reopened)
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED || $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Check if project can be modified
     */
    public function canBeModified(): bool
    {
        return !$this->isClosed();
    }

    /**
     * Close the project
     */
    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Archive the project
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Reopen a closed project
     */
    public function reopen(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Get project member by user ID
     */
    public function getMember(int $userId): ?object
    {
        return $this->members()->where('users.id', $userId)->first();
    }
}
