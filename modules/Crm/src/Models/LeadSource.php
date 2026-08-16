<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LeadSource extends Model
{
    protected $table = 'crm_lead_sources';

    protected $fillable = ['name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_source_id');
    }

    /**
     * @return list<string>
     */
    public static function defaultNames(): array
    {
        return [
            'Website',
            'Referral',
            'LinkedIn',
            'WhatsApp',
            'Cold Call',
            'Campaign',
            'Upwork',
            'Freelance',
            'Other',
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::defaultNames() as $name) {
            static::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }
    }

    public static function findOrCreateByName(?string $name): ?self
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = Str::lower(preg_replace('/\s+/', '-', $name) ?: 'source');
        }

        $existing = static::query()
            ->where(function ($q) use ($slug, $name) {
                $q->where('slug', $slug)
                    ->orWhereRaw('LOWER(name) = ?', [Str::lower($name)]);
            })
            ->first();

        if ($existing) {
            if (!$existing->is_active) {
                $existing->forceFill(['is_active' => true])->save();
            }

            return $existing;
        }

        return static::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
}
