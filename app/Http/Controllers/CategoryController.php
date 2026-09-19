<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\IndexRequest;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @throws \Throwable
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $categories = Category::query()
            ->when($request->input('search'), function ($query, string $search) {
                $query->filterName($search);
            })
            ->paginate(
                perPage: $request->integer('per_page'),
                page: $request->integer('page'),
            );

        return $categories->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $category->toResource()
            ->response()
            ->setStatusCode(ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResource
    {
        return $category->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Category $category): JsonResource
    {
        $category->update($request->validated());

        return $category->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
