<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;

it('should be able to soft delete a category', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create();

    deleteJson(route('categories.destroy', $category))
        ->assertNoContent();

    assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

it('should not be able to delete a category when unauthenticated', function () {
    $category = Category::factory()->create();

    deleteJson(route('categories.destroy', $category))->assertUnauthorized();

    assertDatabaseHas('categories', [
        'id' => $category->id,
        'deleted_at' => null,
    ]);
});
