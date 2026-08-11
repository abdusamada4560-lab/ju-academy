<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'recipient_id',
        'sender_id',
        'project_id',
        'work_package_id',
        'notification_type',
        'title',
        'message',
        'is_read',
        'read_at',
        'action_url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const TYPE_TASK_ASSIGNED = 'task_assigned';
    const TYPE_REVIEW_REQUEST = 'review_request';
    const TYPE_APPROVAL = 'approval';
    const TYPE_DUE_DATE_REMINDER = 'due_date_reminder';
    const TYPE_PROJECT_UPDATE = 'project_update';
    const TYPE_COMMENT_MENTION = 'comment_mention';
    const TYPE_DOCUMENT_SHARED = 'document_shared';

    /**
     * Get the user who receives this notification
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the user who sent this notification
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the related project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the related work package
     */
    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'work_package_id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
