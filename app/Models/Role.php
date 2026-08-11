<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'role_name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Check if role is a specific type
     */
    public function isDirector(): bool
    {
        return $this->role_name === 'ICT Director';
    }

    public function isTeamLeader(): bool
    {
        return $this->role_name === 'Development Team Leader';
    }

    public function isProjectLeader(): bool
    {
        return $this->role_name === 'Project Leader';
    }

    public function isDeveloper(): bool
    {
        return $this->role_name === 'Developer';
    }
}
