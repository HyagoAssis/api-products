<?php

namespace App\Support\Search;

use App\Http\Requests\Product\IndexRequest;

class ProductSearchParams
{
    public function __construct(
        public ?string $search = null,
        public ?int $categoryId = null,
        public bool $hasStock = false,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}

    /**
     * Build the search parameters from the index request.
     */
    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            search: $request->input('search'),
            categoryId: $request->filled('category_id') ? $request->integer('category_id') : null,
            hasStock: $request->boolean('has_stock'),
            minPrice: $request->filled('min_price') ? (float) $request->input('min_price') : null,
            maxPrice: $request->filled('max_price') ? (float) $request->input('max_price') : null,
            page: $request->integer('page'),
            perPage: $request->integer('per_page'),
        );
    }
}
