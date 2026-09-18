<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Client;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

function elasticClient(): Client
{
    return app(ClientBuilderInterface::class)->default();
}

beforeEach(function () {
    config([
        'scout.driver' => 'elastic',
        'elastic.scout_driver.refresh_documents' => true,
    ]);

    try {
        elasticClient()->info();
    } catch (Throwable $e) {
        $this->markTestSkipped('Elasticsearch indisponivel: '.$e->getMessage());
    }

    // Isola o indice entre os testes.
    try {
        elasticClient()->indices()->delete(['index' => (new Product)->searchableAs()]);
    } catch (Throwable) {
        // O indice ainda nao existe.
    }
});

it('should be able to list products with the correct data', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create();

    getJson(route('products.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', $product->name)
        ->assertJsonPath('data.0.description', $product->description)
        ->assertJsonPath('data.0.price', fn ($price) => (float) $price === (float) $product->price)
        ->assertJsonPath('data.0.category_id', $product->category_id)
        ->assertJsonPath('data.0.stock', $product->stock);
});

it('should paginate using the custom perPage and page parameters', function () {
    Sanctum::actingAs(User::factory()->create());

    Product::factory()->count(15)->create();

    $response = getJson(route('products.index', ['perPage' => 5, 'page' => 2]));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 15);
});

it('should filter products by the search term', function () {
    Sanctum::actingAs(User::factory()->create());

    $matching = Product::factory()->create(['name' => 'Notebook Gamer']);
    Product::factory()->create(['name' => 'Cadeira de Escritorio']);

    $response = getJson(route('products.index', ['search' => 'Notebook']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('should filter products by category', function () {
    Sanctum::actingAs(User::factory()->create());

    $eletronicos = Category::factory()->create();
    $moveis = Category::factory()->create();

    $matching = Product::factory()->create(['category_id' => $eletronicos->id]);
    Product::factory()->create(['category_id' => $moveis->id]);

    $response = getJson(route('products.index', ['category_id' => $eletronicos->id]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('should filter products that have stock available', function () {
    Sanctum::actingAs(User::factory()->create());

    $available = Product::factory()->create(['stock' => 5]);
    Product::factory()->create(['stock' => 0]);

    $response = getJson(route('products.index', ['has_stock' => true]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $available->id);
});

it('should not filter by stock when has_stock is false', function () {
    Sanctum::actingAs(User::factory()->create());

    Product::factory()->create(['stock' => 5]);
    Product::factory()->create(['stock' => 0]);

    getJson(route('products.index', ['has_stock' => false]))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('should filter products by the minimum price', function () {
    Sanctum::actingAs(User::factory()->create());

    $expensive = Product::factory()->create(['price' => '150.00']);
    Product::factory()->create(['price' => '50.00']);

    $response = getJson(route('products.index', ['min_price' => '100.00']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $expensive->id);
});

it('should filter products by the maximum price', function () {
    Sanctum::actingAs(User::factory()->create());

    $cheap = Product::factory()->create(['price' => '50.00']);
    Product::factory()->create(['price' => '150.00']);

    $response = getJson(route('products.index', ['max_price' => '100.00']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $cheap->id);
});

it('should filter products within a price range', function () {
    Sanctum::actingAs(User::factory()->create());

    $inRange = Product::factory()->create(['price' => '100.00']);
    Product::factory()->create(['price' => '10.00']);
    Product::factory()->create(['price' => '500.00']);

    $response = getJson(route('products.index', ['min_price' => '50.00', 'max_price' => '200.00']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inRange->id);
});

it('should return the category relation for each product', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    getJson(route('products.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.category.id', $category->id)
        ->assertJsonPath('data.0.category.name', $category->name);
});

it('should not be able to list products when unauthenticated', function () {
    getJson(route('products.index'))->assertUnauthorized();
});
