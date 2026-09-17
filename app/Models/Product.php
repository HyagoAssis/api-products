<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product query()
 * @mixin \Eloquent
 */
#[Table('products', 'id')]
#[Fillable('name', 'description', 'price', 'category', 'stock')]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }

    /**
     * Filter products whose name matches the given term.
     */
    #[Scope]
    protected function filterName(Builder $query, string $name): void
    {
        $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($name).'%']);
    }

    /**
     * Filter products by category.
     */
    #[Scope]
    protected function filterCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Filter products that still have stock available.
     */
    #[Scope]
    protected function filterHasStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * Filter products with a price greater than or equal to the given value.
     */
    #[Scope]
    protected function filterMinPrice(Builder $query, string $minPrice): void
    {
        $query->where('price', '>=', $minPrice);
    }

    /**
     * Filter products with a price less than or equal to the given value.
     */
    #[Scope]
    protected function filterMaxPrice(Builder $query, string $maxPrice): void
    {
        $query->where('price', '<=', $maxPrice);
    }
}
