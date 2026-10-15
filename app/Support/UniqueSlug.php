<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * Generate a slug that is unique for the given model.
     */
    public static function for(Model $model, string $value, ?int $ignoreKey = null): string
    {
        $base = Str::slug($value) ?: 'item';

        $slug = $base;
        $suffix = 2;

        while (static::exists($model, $slug, $ignoreKey)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function exists(Model $model, string $slug, ?int $ignoreKey): bool
    {
        return $model->newQuery()
            ->where('slug', $slug)
            ->when(
                $ignoreKey !== null,
                fn ($query) => $query->where($model->getKeyName(), '!=', $ignoreKey)
            )
            ->exists();
    }
}
