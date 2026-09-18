<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

it('should be able to update a category', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create();

    $response = putJson(route('categories.update', $category), [
        'name' => 'Categoria Atualizada',
        'description' => 'Descricao atualizada',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.name', 'Categoria Atualizada')
        ->assertJsonPath('data.description', 'Descricao atualizada');

    assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Categoria Atualizada',
        'description' => 'Descricao atualizada',
    ]);
});

it('should be able to partially update a category', function () {
    Sanctum::actingAs(User::factory()->create());

    $category = Category::factory()->create([
        'name' => 'Nome Original',
        'description' => 'Descricao Original',
    ]);

    putJson(route('categories.update', $category), [
        'description' => 'Descricao Nova',
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Nome Original')
        ->assertJsonPath('data.description', 'Descricao Nova');

    assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Nome Original',
        'description' => 'Descricao Nova',
    ]);
});

it('should not be able to update a category when unauthenticated', function () {
    $category = Category::factory()->create(['name' => 'Nome Original']);

    putJson(route('categories.update', $category), [
        'name' => 'Novo Nome',
    ])->assertUnauthorized();

    assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Nome Original',
    ]);
});

describe('validation rules', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create());
        $this->category = Category::factory()->create();
    });

    test('name:string', function () {
        putJson(route('categories.update', $this->category), [
            'name' => ['array'],
        ])->assertJsonValidationErrors(['name' => 'string']);
    });

    test('name:max', function () {
        putJson(route('categories.update', $this->category), [
            'name' => str_repeat('a', 256),
        ])->assertJsonValidationErrors(['name' => '255']);
    });

    test('description:string', function () {
        putJson(route('categories.update', $this->category), [
            'description' => ['array'],
        ])->assertJsonValidationErrors(['description' => 'string']);
    });

    test('description:max', function () {
        putJson(route('categories.update', $this->category), [
            'description' => str_repeat('a', 1001),
        ])->assertJsonValidationErrors(['description' => '1000']);
    });
});
