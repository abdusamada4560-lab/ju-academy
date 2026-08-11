<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_log';

    protected $fillable = [
        'project_id',
        'work_package_id',
        'user_id',
        'action_type',
        'old_value',
        'new_value',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_COMMENTED = 'commented';
    const ACTION_DOCUMENT_UPLOADED = 'document_uploaded';
    const ACTION_PROJECT_CLOSED = 'project_closed';

    /**
     * Get the project this activity belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the work package this activity is about
     */
    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'work_package_id');
    }

    /**
     * Get the user who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
