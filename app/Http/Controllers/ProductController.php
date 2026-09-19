<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use App\Support\Search\ElasticsearchHelper;
use App\Support\Search\ProductSearchParams;
use Elastic\Transport\Exception\TransportException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

use function Sentry\captureException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @throws Throwable
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $params = ProductSearchParams::fromRequest($request);

        $products = $this->searchProducts($params);

        return $products->toResourceCollection();
    }

    /**
     * Search products via Elasticsearch, falling back to the database when it is unavailable.
     *
     * @return LengthAwarePaginator<int, Product>
     *
     * @throws Throwable
     */
    private function searchProducts(ProductSearchParams $params): LengthAwarePaginator
    {
        try {
            return Product::searchQuery(ElasticsearchHelper::buildQuery($params))
                ->trackTotalHits(true)
                ->load(['category'])
                ->paginate($params->perPage, 'page', $params->page)
                ->onlyModels();
        } catch (Throwable $exception) {
            if (! $exception instanceof TransportException) {
                throw $exception;
            }

            captureException($exception);

            return Product::buildFilteredQuery($params)
                ->with('category')
                ->paginate(perPage: $params->perPage, page: $params->page);
        }
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
