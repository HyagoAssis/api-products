<?php

use App\Models\Product;
use App\Models\User;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

/**
 * Force the Elasticsearch engine to fail and assert the listing degrades to the database.
 */
function failElasticsearch(): void
{
    config(['scout.driver' => 'elastic']);

    test()->mock(DocumentManager::class)
        ->shouldReceive('search')
        ->andThrow(new NoNodeAvailableException('Elasticsearch indisponivel'));
}

it('should fall back to the database when elasticsearch is unavailable', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create();

    failElasticsearch();

    getJson(route('products.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.category.id', $product->category_id);
});

it('should keep applying filters on the database fallback', function () {
    Sanctum::actingAs(User::factory()->create());

    $matching = Product::factory()->create(['name' => 'Notebook Gamer']);
    Product::factory()->create(['name' => 'Cadeira de Escritorio']);

    failElasticsearch();

    getJson(route('products.index', ['search' => 'Notebook']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});
