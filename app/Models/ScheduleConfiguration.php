<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Validation rules
    public static function rules(): array
    {
        return [
            'key' => 'required|string|max:255|unique:schedule_configurations,key',
            'value' => 'required|string',
            'type' => 'required|in:integer,float,boolean,json,string',
            'description' => 'nullable|string',
        ];
    }

    // Type casting methods
    public function getTypedValue()
    {
        return match ($this->type) {
            'integer' => $this->castToInteger(),
            'float' => $this->castToFloat(),
            'boolean' => $this->castToBoolean(),
            'json' => $this->castToJson(),
            default => $this->value,
        };
    }

    public function castToInteger(): int
    {
        return (int) $this->value;
    }

    public function castToFloat(): float
    {
        return (float) $this->value;
    }

    public function castToBoolean(): bool
    {
        if (is_bool($this->value)) {
            return $this->value;
        }

        $value = strtolower($this->value);

        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }

    public function castToJson(): array
    {
        if (is_array($this->value)) {
            return $this->value;
        }

        $decoded = json_decode($this->value, true);

        return is_array($decoded) ? $decoded : [];
    }

    // Helper methods
    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        return $config->getTypedValue();
    }

    public static function setValue(string $key, $value, string $type = 'string', ?string $description = null): self
    {
        // Convert value to string for storage
        $stringValue = match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function getAll(): array
    {
        return self::all()->mapWithKeys(function ($config) {
            return [$config->key => $config->getTypedValue()];
        })->toArray();
    }

    public static function getAllGrouped(): array
    {
        return self::all()->groupBy('type')->map(function ($group) {
            return $group->mapWithKeys(function ($config) {
                return [$config->key => [
                    'value' => $config->getTypedValue(),
                    'description' => $config->description,
                ]];
            });
        })->toArray();
    }
}
