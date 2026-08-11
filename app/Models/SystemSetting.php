<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'category',
        'description',
        'data_type',
        'updated_by',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CATEGORY_EMAIL = 'email';
    const CATEGORY_NOTIFICATIONS = 'notifications';
    const CATEGORY_SCHEDULING = 'scheduling';
    const CATEGORY_REPORTS = 'reports';
    const CATEGORY_CALENDAR = 'calendar';

    const DATA_TYPE_STRING = 'string';
    const DATA_TYPE_INTEGER = 'integer';
    const DATA_TYPE_BOOLEAN = 'boolean';
    const DATA_TYPE_JSON = 'json';
    const DATA_TYPE_DECIMAL = 'decimal';

    /**
     * Get the user who last updated this setting
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)
            ->where('is_active', true)
            ->first();
        
        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->setting_value, $setting->data_type);
    }

    /**
     * Cast value based on data type
     */
    public static function castValue($value, string $dataType)
    {
        return match ($dataType) {
            self::DATA_TYPE_INTEGER => (int) $value,
            self::DATA_TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::DATA_TYPE_DECIMAL => (float) $value,
            self::DATA_TYPE_JSON => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Check if setting is modifiable from UI
     */
    public function isEditable(): bool
    {
        return !$this->is_system;
    }
}
