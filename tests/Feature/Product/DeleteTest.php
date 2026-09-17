<?php

use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

it('should be able to delete a product', function () {
    Sanctum::actingAs(User::factory()->create());

    $product = Product::factory()->create();

    deleteJson(route('products.destroy', $product))
        ->assertNoContent();

    assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

it('should not be able to delete a product when unauthenticated', function () {
    $product = Product::factory()->create();

    deleteJson(route('products.destroy', $product))->assertUnauthorized();

    assertDatabaseHas('products', [
        'id' => $product->id,
    ]);
});
