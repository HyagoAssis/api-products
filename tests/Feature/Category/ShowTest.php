<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('should be able to show a category with the correct data', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create();

    getJson(route('categories.show', $category))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.name', $category->name)
        ->assertJsonPath('data.description', $category->description);
});

it('should not be able to show a category when unauthenticated', function () {
    $category = Category::factory()->create();

    getJson(route('categories.show', $category))->assertUnauthorized();
});
