<?php

use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('should be able to show a product with the correct data', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create();

    getJson(route('products.show', $product))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', $product->name)
        ->assertJsonPath('data.description', $product->description)
        ->assertJsonPath('data.price', fn ($price) => (float) $price === (float) $product->price)
        ->assertJsonPath('data.category_id', $product->category_id)
        ->assertJsonPath('data.stock', $product->stock);
});

it('should not be able to show a product when unauthenticated', function () {
    $product = Product::factory()->create();

    getJson(route('products.show', $product))->assertUnauthorized();
});
