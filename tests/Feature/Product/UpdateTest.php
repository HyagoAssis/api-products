<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

it('should be able to update a product', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create();
    $category = Category::factory()->create();

    $response = putJson(route('products.update', $product), [
        'name' => 'Produto Atualizado',
        'description' => 'Descricao atualizada',
        'price' => '299.90',
        'category_id' => $category->id,
        'stock' => 42,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', 'Produto Atualizado')
        ->assertJsonPath('data.description', 'Descricao atualizada')
        ->assertJsonPath('data.price', '299.90')
        ->assertJsonPath('data.category_id', $category->id)
        ->assertJsonPath('data.stock', 42);

    assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Produto Atualizado',
        'description' => 'Descricao atualizada',
        'price' => '299.90',
        'category_id' => $category->id,
        'stock' => 42,
    ]);
});

it('should be able to partially update a product', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create([
        'name' => 'Nome Original',
        'price' => '100.00',
    ]);

    putJson(route('products.update', $product), [
        'price' => '150.00',
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Nome Original')
        ->assertJsonPath('data.price', '150.00');

    assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Nome Original',
        'price' => '150.00',
    ]);
});

it('should not be able to update a product when unauthenticated', function () {
    $product = Product::factory()->create(['name' => 'Nome Original']);

    putJson(route('products.update', $product), [
        'name' => 'Novo Nome',
    ])->assertUnauthorized();

    assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Nome Original',
    ]);
});

describe('validation rules', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create());
        $this->product = Product::factory()->create();
    });

    test('name:string', function () {
        putJson(route('products.update', $this->product), [
            'name' => ['array'],
        ])->assertJsonValidationErrors(['name' => 'string']);
    });

    test('name:max', function () {
        putJson(route('products.update', $this->product), [
            'name' => str_repeat('a', 256),
        ])->assertJsonValidationErrors(['name' => '255']);
    });

    test('description:string', function () {
        putJson(route('products.update', $this->product), [
            'description' => ['array'],
        ])->assertJsonValidationErrors(['description' => 'string']);
    });

    test('description:max', function () {
        putJson(route('products.update', $this->product), [
            'description' => str_repeat('a', 1001),
        ])->assertJsonValidationErrors(['description' => '1000']);
    });

    test('price:decimal', function () {
        putJson(route('products.update', $this->product), [
            'price' => '199.999',
        ])->assertJsonValidationErrors(['price' => 'decimal']);
    });

    test('category_id:integer', function () {
        putJson(route('products.update', $this->product), [
            'category_id' => 'not-an-integer',
        ])->assertJsonValidationErrors(['category_id' => 'integer']);
    });

    test('category_id:exists', function () {
        putJson(route('products.update', $this->product), [
            'category_id' => 999999,
        ])->assertJsonValidationErrors(['category_id' => 'invalid']);
    });

    test('stock:integer', function () {
        putJson(route('products.update', $this->product), [
            'stock' => 'not-an-integer',
        ])->assertJsonValidationErrors(['stock' => 'integer']);
    });
});
