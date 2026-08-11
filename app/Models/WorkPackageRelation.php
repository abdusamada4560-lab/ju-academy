<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPackageRelation extends Model
{
    protected $table = 'work_package_relations';

    protected $fillable = [
        'source_work_package_id',
        'target_work_package_id',
        'relation_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const TYPE_BLOCKS = 'blocks';
    const TYPE_BLOCKED_BY = 'blocked_by';
    const TYPE_PRECEDES = 'precedes';
    const TYPE_FOLLOWS = 'follows';

    /**
     * Get the source work package
     */
    public function sourceWorkPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'source_work_package_id');
    }

    /**
     * Get the target work package
     */
    public function targetWorkPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'target_work_package_id');
    }
}
