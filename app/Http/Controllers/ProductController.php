<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use App\Support\Search\ElasticsearchHelper;
use App\Support\Search\ProductSearchParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @throws \Throwable
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $params = ProductSearchParams::fromRequest($request);

        $products = Product::searchQuery(ElasticsearchHelper::buildQuery($params))
            ->trackTotalHits(true)
            ->load(['category'])
            ->paginate($params->perPage, 'page', $params->page)
            ->onlyModels();

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
