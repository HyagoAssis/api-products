<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('should be able to store a new category', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = postJson(route('categories.store'), [
        'name' => 'Categoria Teste',
        'description' => 'Descricao da categoria teste',
    ]);

    $response->assertCreated();

    $response->assertJsonPath('data.name', 'Categoria Teste')
        ->assertJsonPath('data.description', 'Descricao da categoria teste');

    assertDatabaseHas('categories', [
        'id' => $response->json('data.id'),
        'name' => 'Categoria Teste',
        'description' => 'Descricao da categoria teste',
    ]);
});

it('should be able to store a category without a description', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = postJson(route('categories.store'), [
        'name' => 'Categoria Sem Descricao',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.description', null);

    assertDatabaseHas('categories', [
        'id' => $response->json('data.id'),
        'name' => 'Categoria Sem Descricao',
        'description' => null,
    ]);
});

it('should not be able to store a category when unauthenticated', function () {
    postJson(route('categories.store'), [
        'name' => 'Categoria Teste',
        'description' => 'Descricao da categoria teste',
    ])->assertUnauthorized();
});

describe('validation rules', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create());
    });

    test('name:required', function () {
        postJson(route('categories.store'), [
            'description' => 'Descricao da categoria teste',
        ])->assertJsonValidationErrors(['name' => 'required']);
    });

    test('name:string', function () {
        postJson(route('categories.store'), [
            'name' => ['array'],
        ])->assertJsonValidationErrors(['name' => 'string']);
    });

    test('name:max', function () {
        postJson(route('categories.store'), [
            'name' => str_repeat('a', 256),
        ])->assertJsonValidationErrors(['name' => '255']);
    });

    test('description:string', function () {
        postJson(route('categories.store'), [
            'description' => ['array'],
        ])->assertJsonValidationErrors(['description' => 'string']);
    });

    test('description:max', function () {
        postJson(route('categories.store'), [
            'description' => str_repeat('a', 1001),
        ])->assertJsonValidationErrors(['description' => '1000']);
    });
});
