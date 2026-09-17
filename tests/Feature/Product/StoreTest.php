<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('should be able to store a new product', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = postJson(route('products.store'), [
        'name' => 'Produto Teste',
        'description' => 'Descricao do produto teste',
        'price' => '199.90',
        'category' => 'Categoria Teste',
        'stock' => 10,
    ]);

    $response->assertCreated();

    $response->assertJsonPath('data.name', 'Produto Teste')
        ->assertJsonPath('data.description', 'Descricao do produto teste')
        ->assertJsonPath('data.price', '199.90')
        ->assertJsonPath('data.category', 'Categoria Teste')
        ->assertJsonPath('data.stock', 10);

    assertDatabaseHas('products', [
        'id' => $response->json('data.id'),
        'name' => 'Produto Teste',
        'description' => 'Descricao do produto teste',
        'price' => '199.90',
        'category' => 'Categoria Teste',
        'stock' => 10,
    ]);
});

it('should not be able to store a product when unauthenticated', function () {
    postJson(route('products.store'), [
        'name' => 'Produto Teste',
        'description' => 'Descricao do produto teste',
        'price' => '199.90',
        'category' => 'Categoria Teste',
        'stock' => 10,
    ])->assertUnauthorized();
});

describe('validation rules', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create());
    });

    test('name:required', function () {
        postJson(route('products.store'), [
            'description' => 'Descricao do produto teste',
            'price' => '199.90',
            'category' => 'Categoria Teste',
        ])->assertJsonValidationErrors(['name' => 'required']);
    });

    test('name:string', function () {
        postJson(route('products.store'), [
            'name' => ['array'],
        ])->assertJsonValidationErrors(['name' => 'string']);
    });

    test('name:max', function () {
        postJson(route('products.store'), [
            'name' => str_repeat('a', 256),
        ])->assertJsonValidationErrors(['name' => '255']);
    });

    test('description:required', function () {
        postJson(route('products.store'), [
            'name' => 'Produto Teste',
        ])->assertJsonValidationErrors(['description' => 'required']);
    });

    test('description:string', function () {
        postJson(route('products.store'), [
            'description' => ['array'],
        ])->assertJsonValidationErrors(['description' => 'string']);
    });

    test('description:max', function () {
        postJson(route('products.store'), [
            'description' => str_repeat('a', 1001),
        ])->assertJsonValidationErrors(['description' => '1000']);
    });

    test('price:required', function () {
        postJson(route('products.store'), [
            'name' => 'Produto Teste',
        ])->assertJsonValidationErrors(['price' => 'required']);
    });

    test('price:decimal', function () {
        postJson(route('products.store'), [
            'price' => '199.999',
        ])->assertJsonValidationErrors(['price' => 'decimal']);
    });

    test('category:required', function () {
        postJson(route('products.store'), [
            'name' => 'Produto Teste',
        ])->assertJsonValidationErrors(['category' => 'required']);
    });

    test('category:string', function () {
        postJson(route('products.store'), [
            'category' => ['array'],
        ])->assertJsonValidationErrors(['category' => 'string']);
    });

    test('category:max', function () {
        postJson(route('products.store'), [
            'category' => str_repeat('a', 256),
        ])->assertJsonValidationErrors(['category' => '255']);
    });

    test('stock:integer', function () {
        postJson(route('products.store'), [
            'stock' => 'not-an-integer',
        ])->assertJsonValidationErrors(['stock' => 'integer']);
    });
});
