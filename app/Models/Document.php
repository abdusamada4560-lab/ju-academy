<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'project_id',
        'work_package_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
        'version',
        'description',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project this document belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the work package this document is associated with (optional)
     */
    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'work_package_id');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Archive the document
     */
    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }

    /**
     * Get the full file URL
     */
    public function getFileUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Check if user can access this document (Business Rule 11: only project members access documents)
     */
    public function canBeAccessedBy(User $user): bool
    {
        return $this->project->members()->where('users.id', $user->id)->exists() || 
               $user->isDirector() || 
               $user->isTeamLeader();
    }
}
