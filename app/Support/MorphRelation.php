<?php

namespace App\Support;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class MorphRelation
{
    const string PRODUCT = 'product';

    const string USER = 'user';

    /**
     * The morph alias to model class map.
     *
     * @return array<string, class-string<Model>>
     */
    public static function morphMap(): array
    {
        return [
            self::PRODUCT => Models\Product::class,
            self::USER => Models\User::class,
        ];
    }

    /**
     * Resolve the alias for the given class name, or return it unchanged when not mapped.
     */
    public static function alias(?string $className): ?string
    {
        return collect(self::morphMap())->filter(function ($item) use ($className) {
            return $item === $className;
        })->keys()->first() ?? $className;
    }
}
