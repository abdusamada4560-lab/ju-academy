<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'project_id',
        'work_package_id',
        'author_id',
        'parent_comment_id',
        'comment_text',
        'is_edited',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project this comment belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the work package this comment is about (optional)
     */
    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'work_package_id');
    }

    /**
     * Get the comment author
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent comment (for nested comments)
     */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    /**
     * Mark comment as edited
     */
    public function markAsEdited(): void
    {
        $this->update(['is_edited' => true]);
    }
}
