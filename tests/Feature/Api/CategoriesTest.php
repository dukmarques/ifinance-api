<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('get all categories', function () {
    Category::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->getJson("/api/categories");
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');

    expect(Category::query()->count())->toBe(3);
});

it('get all categories without login', function () {
    $response = getJson('/api/categories');
    $response->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
});

it('get a category by id', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->getJson("/api/categories/{$category->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $category->id,
            'name' => $category->name,
            'user_id' => $this->user->id
        ]);
});

it('get a category by non-existent id', function () {
    $response = actingAs($this->user)->getJson("/api/categories/123");
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Category not found'
        ]);
});

it('create a category', function () {
    $name = fake()->word;

    $response = actingAs($this->user)->postJson("/api/categories", ['name' => $name]);
    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => $name,
            'user_id' => $this->user->id
        ]);
});

it('create a category with invalid name', function () {
    $response = actingAs($this->user)->postJson("/api/categories", ['name' => 'a']);
    $response->assertStatus(400);

    $response = actingAs($this->user)->postJson("/api/categories");
    $response->assertStatus(400);
});

it('update a category', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    $name = fake()->word;

    $response = actingAs($this->user)->putJson("/api/categories/{$category->id}", ['name' => $name]);
    $response->assertStatus(200)
        ->assertJsonFragment(['name' => $name]);
});

it('update a category with invalid name', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->putJson("/api/categories/{$category->id}", ['name' => '']);
    $response->assertStatus(400);

    $response = actingAs($this->user)->putJson("/api/categories/{$category->id}", ['name' => 'a']);
    $response->assertStatus(400);
});

it('update a non-existent category', function () {
    $name = fake()->word;

    $response = actingAs($this->user)->putJson("/api/categories/123", ['name' => $name]);
    $response->assertStatus(404)
        ->assertJsonFragment(['message' => 'Category not found']);
});

it('delete a category', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->deleteJson("/api/categories/{$category->id}");
    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Category deleted successfully']);
});

it('delete a non-existent category', function () {
    $response = actingAs($this->user)->deleteJson("/api/categories/123");
    $response->assertStatus(404)
        ->assertJsonFragment(['message' => 'Category not found']);
});
