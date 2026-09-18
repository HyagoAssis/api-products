<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @throws Throwable
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $products = Product::query()
            ->with(['category'])
            ->when($request->input('search'), function ($query, string $search) {
                $query->filterName($search);
            })
            ->when($request->input('category_id'), function ($query, string $categoryId) {
                $query->filterCategory((int) $categoryId);
            })
            ->when($request->boolean('has_stock'), function ($query) {
                $query->filterHasStock();
            })
            ->when($request->input('min_price'), function ($query, string $minPrice) {
                $query->filterMinPrice($minPrice);
            })
            ->when($request->input('max_price'), function ($query, string $maxPrice) {
                $query->filterMaxPrice($maxPrice);
            })
            ->paginate(
                perPage: $request->integer('perPage'),
                page: $request->integer('page'),
            );

        return $products->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        $product->load('category');

        return $product->toResource()
            ->response()
            ->setStatusCode(ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResource
    {
        $product->load('category');

        return $product->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Product $product): JsonResource
    {
        $product->update($request->validated());

        $product->load('category');

        return $product->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
