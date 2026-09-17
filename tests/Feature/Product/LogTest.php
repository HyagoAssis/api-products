<?php

use App\Models\Product;
use App\Models\User;
use App\Models\UserLog;

use function Pest\Laravel\actingAs;

it('creates a CREATED log when a product is created', function () {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::factory()->create(['name' => 'Produto Teste']);

    $log = UserLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->model_type)->toBe('product')
        ->and($log->model_id)->toBe($product->id)
        ->and($log->operation)->toBe('CREATED')
        ->and($log->old_values)->toBeNull()
        ->and($log->new_values)->toMatchArray(['name' => 'Produto Teste']);
});

it('creates an UPDATED log with only the changed attributes', function () {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::factory()->create(['price' => '100.00']);

    $product->update(['price' => '150.00']);

    $log = UserLog::query()->where('operation', 'UPDATED')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->model_type)->toBe('product')
        ->and($log->model_id)->toBe($product->id)
        ->and($log->old_values)->toMatchArray(['price' => '100.00'])
        ->and($log->new_values)->toMatchArray(['price' => '150.00']);
});

it('creates a DELETED log when a product is deleted', function () {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::factory()->create(['name' => 'Para Deletar']);

    $product->delete();

    $log = UserLog::query()->where('operation', 'DELETED')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->model_type)->toBe('product')
        ->and($log->model_id)->toBe($product->id)
        ->and($log->old_values)->toMatchArray(['name' => 'Para Deletar'])
        ->and($log->new_values)->toBeNull();
});

it('creates a log without a user when unauthenticated', function () {
    $product = Product::factory()->create();

    $log = UserLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBeNull()
        ->and($log->model_id)->toBe($product->id)
        ->and($log->operation)->toBe('CREATED');
});
