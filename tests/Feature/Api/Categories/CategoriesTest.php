<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('get all categories', function () {
    Category::factory()->count(3)->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->getJson("/api/categories")
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(3, 'data');

    expect(Category::query()->count())->toBe(3);
});

it('get all categories without login', function () {
    getJson('/api/categories')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
});

it('get a category by id', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->getJson("/api/categories/{$category->id}")
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'id' => $category->id,
            'name' => $category->name,
            'user_id' => $this->user->id
        ]);
});

it('get a category by non-existent id', function () {
    actingAs($this->user)
        ->getJson("/api/categories/123")
        ->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonFragment([
            'message' => 'Resource not found'
        ]);
});

it('create a category', function () {
    $name = fake()->word;

    actingAs($this->user)
        ->postJson("/api/categories", ['name' => $name])
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment([
            'name' => $name,
            'user_id' => $this->user->id
        ]);
});

it('create a category with invalid name', function () {
    actingAs($this->user)
        ->postJson("/api/categories", ['name' => 'a'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    actingAs($this->user)
        ->postJson("/api/categories")
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

it('update a category', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    $name = fake()->word;

    actingAs($this->user)
        ->putJson("/api/categories/{$category->id}", ['name' => $name])
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment(['name' => $name]);
});

it('update a category with invalid name', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->putJson("/api/categories/{$category->id}", ['name' => ''])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    actingAs($this->user)
        ->putJson("/api/categories/{$category->id}", ['name' => 'a'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

it('update a non-existent category', function () {
    $name = fake()->word;

    actingAs($this->user)
        ->putJson("/api/categories/123", ['name' => $name])
        ->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonFragment(['message' => 'Resource not found']);
});

it('delete a category', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->deleteJson("/api/categories/{$category->id}")
        ->assertStatus(Response::HTTP_NO_CONTENT);
});

it('delete a non-existent category', function () {
    actingAs($this->user)
        ->deleteJson("/api/categories/123")
        ->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonFragment(['message' => 'Resource not found']);
});
