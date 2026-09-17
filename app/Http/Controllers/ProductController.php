<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->when($request->input('search'), function ($query, string $search) {
                $query->filterName($search);
            })
            ->when($request->input('category'), function ($query, string $category) {
                $query->filterCategory($category);
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

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product);
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
