<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, actingAs};
use App\Models\User;
use App\Models\Category;
use App\Models\Revenues;
use App\Models\RevenuesOverrides;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id
    ]);
    $this->date = \Carbon\Carbon::now();
});

it('get all revenues', function () {
    Revenues::factory()
        ->has(
            RevenuesOverrides::factory()->count(1),
            'overrides'
        )
        ->count(4)
        ->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
    ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(4);
});

it('get revenues without override', function () {
    Revenues::factory()
        ->hasOverrides(
            1,
            function () {
                return [
                    'receiving_date' => \Carbon\Carbon::now()->addMonths(2),
                ];
            },
        )
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(0, '0.overrides');
});

it('get revenues with override', function () {
    Revenues::factory()
        ->has(
            RevenuesOverrides::factory()->count(1),
            'overrides'
        )
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray();
});

it('get a revenue by id', function () {
    $revenue = Revenues::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->getJson("/api/revenues/{$revenue->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => $revenue->title,
            'amount' => $revenue->amount,
            'user_id' => $this->user->id,
            'description' => $revenue->description,
        ]);
});

it('get a non-existent revenue', function () {
    $response = actingAs($this->user)->getJson("/api/revenues/123");
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Revenue not found'
        ]);
});
