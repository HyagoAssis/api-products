<?php

namespace App\Models;

use App\Jobs\LogProductChangesJob;
use App\Support\Search\ProductSearchParams;
use Database\Factories\ProductFactory;
use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @method static ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product query()
 *
 * @property mixed $id
 * @property string $name
 * @property string $description
 * @property numeric $price
 * @property int $category_id
 * @property int $stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Product filterCategory(int $categoryId)
 * @method static Builder<static>|Product filterHasStock()
 * @method static Builder<static>|Product filterMaxPrice(string $maxPrice)
 * @method static Builder<static>|Product filterMinPrice(string $minPrice)
 * @method static Builder<static>|Product filterName(string $name)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePrice($value)
 * @method static Builder<static>|Product whereStock($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('products', 'id')]
#[Fillable('name', 'description', 'price', 'category_id', 'stock')]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Product $product) {
            $product->logChanges('CREATED', null, $product->getAttributes());
        });

        static::updated(function (Product $product) {
            $changes = $product->getChanges();

            $product->logChanges(
                'UPDATED',
                Arr::only($product->getOriginal(), array_keys($changes)),
                $changes,
            );
        });

        static::deleted(function (Product $product) {
            $product->logChanges('DELETED', $product->getOriginal(), null);
        });
    }

    public function logChanges($operation, $oldValues, $newValues): void
    {
        LogProductChangesJob::dispatch(
            $this->getMorphClass(),
            $this->id,
            auth()->id(),
            $operation,
            $oldValues,
            $newValues,
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'category_id' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => [
                'name' => $this->category?->name,
            ],
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }

    /**
     * Eager load the category relation when building the search index.
     *
     * @param  Collection<int, Product>  $models
     * @return Collection<int, Product>
     */
    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->load('category');
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Build an Eloquent query applying the given search filters.
     *
     * @return Builder<static>
     */
    public static function buildFilteredQuery(ProductSearchParams $params): Builder
    {
        return static::query()
            ->when($params->search, function (Builder $query, string $search): void {
                $query->filterName($search);
            })
            ->when($params->categoryId !== null, function (Builder $query) use ($params): void {
                $query->filterCategory($params->categoryId);
            })
            ->when($params->hasStock, function (Builder $query): void {
                $query->filterHasStock();
            })
            ->when($params->minPrice !== null, function (Builder $query) use ($params): void {
                $query->filterMinPrice((string) $params->minPrice);
            })
            ->when($params->maxPrice !== null, function (Builder $query) use ($params): void {
                $query->filterMaxPrice((string) $params->maxPrice);
            });
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
    protected function filterCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
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
