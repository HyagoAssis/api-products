<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('should be able to list categories with the correct data', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create();

    getJson(route('categories.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $category->id)
        ->assertJsonPath('data.0.name', $category->name)
        ->assertJsonPath('data.0.description', $category->description);
});

it('should paginate using the custom perPage and page parameters', function () {
    Sanctum::actingAs(User::factory()->create());

    Category::factory()->count(15)->create();

    $response = getJson(route('categories.index', ['perPage' => 5, 'page' => 2]));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 15);
});

it('should filter categories by the search term', function () {
    Sanctum::actingAs(User::factory()->create());

    $matching = Category::factory()->create(['name' => 'Eletronicos']);
    Category::factory()->create(['name' => 'Moveis']);

    $response = getJson(route('categories.index', ['search' => 'Eletro']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('should not list soft deleted categories', function () {
    Sanctum::actingAs(User::factory()->create());

    Category::factory()->create();
    Category::factory()->create()->delete();

    getJson(route('categories.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('should not be able to list categories when unauthenticated', function () {
    getJson(route('categories.index'))->assertUnauthorized();
});
