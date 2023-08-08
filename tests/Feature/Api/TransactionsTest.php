<?php

use App\Models\Card;
use App\Models\Category;
use App\Models\User;
use App\Models\Transaction;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, actingAs};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->card = Card::factory()->create(['user_id' => $this->user->id]);
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

it('get all transactions', function () {
    Transaction::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'category_id' => $this->category->id
    ]);

    $response = actingAs($this->user)->getJson('/api/transactions');
    $response->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(3);
});

it('get all transactions without login', function () {
    $response = getJson('/api/transactions');
    $response->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
});

it('get transaction by id', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'category_id' => $this->category->id
    ]);

    $response = actingAs($this->user)->getJson("/api/transactions/{$transaction->id}");
    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $transaction->id]);
});

it('create a transaction', function () {
    $data = [
        'title' => fake()->word(),
        'price' => fake()->randomDigit(),
        'type' => fake()->randomElement(['entry', 'exit']),
        'is_owner' => fake()->boolean(),
        'date' => fake()->date(),
        'pay_month' => fake()->date(),
        'paid_out' => fake()->boolean(),
        'card_id' => $this->card->id,
        'category_id' => $this->category->id
    ];

    $response = actingAs($this->user)->postJson('/api/transactions', $data);
    $response->assertStatus(201)
        ->assertJsonFragment($data);
});

it('update a transaction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'category_id' => $this->category->id
    ]);

    $data = [
        'title' => fake()->word(),
        'price' => fake()->randomDigit(),
        'date' => fake()->date(),
        'pay_month' => fake()->date(),
        'paid_out' => fake()->boolean(),
    ];

    $response = actingAs($this->user)->putJson("/api/transactions/{$transaction->id}", $data);
    $response->assertStatus(200)
        ->assertJsonFragment($data);
});

it('update a transaction with invalid id', function () {
    $data = ['title' => fake()->word()];

    $response = actingAs($this->user)->putJson("/api/transactions/123", $data);
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Transaction not found'
        ]);
});

it('update transaction with invalid name', function () {
    $transaction = Transaction::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->putJson("/api/transactions/{$transaction->id}", ['title' => 'a']);
    $response->assertStatus(400);
});

it('delete a transaction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'category_id' => $this->category->id
    ]);

    $response = actingAs($this->user)->deleteJson("/api/transactions/{$transaction->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'message' => 'Transaction deleted successfully'
        ]);
});

it('delete a transaction with invalid id', function () {
    $response = actingAs($this->user)->deleteJson("/api/transactions/123");
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Transaction not found'
        ]);
});
