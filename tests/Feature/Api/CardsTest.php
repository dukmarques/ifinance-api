<?php
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, actingAs};
use App\Models\User;
use App\Models\Card;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('get all cards', function () {
    Card::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    actingAs($this->user)
        ->getJson("/api/cards")
        ->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(3);

    expect(Card::query()->count())->toBe(3);
});

it('get a card by id', function () {
    $card = Card::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->getJson("/api/cards/{$card->id}")
        ->assertStatus(200)
        ->assertJsonFragment([
            'name' => $card->name,
            'closing_date' => $card->closing_date,
            'due_date' => $card->due_date,
            'user_id' => $card->user_id
        ]);
});

it('get a non-existent card', function () {
    actingAs($this->user)
        ->getJson("/api/cards/123")
        ->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Card not found'
        ]);
});

it('register a credit card', function () {
    $data = [
        'name' => fake()->creditCardType(),
        'closing_date' => fake()->date(),
        'due_date' => fake()->date()
    ];

    actingAs($this->user)
        ->postJson("/api/cards", $data)
        ->assertStatus(201)
        ->assertJsonFragment($data);
});

it('get all cards without login', function() {
    getJson("/api/cards")
        ->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
});

it('update a card', function () {
    $card = Card::factory()->create(['user_id' => $this->user->id]);
    $data = [
        'name' => fake()->creditCardType(),
        'closing_date' => fake()->date(),
        'due_date' => fake()->date()
    ];

    actingAs($this->user)
        ->putJson("/api/cards/{$card->id}", $data)
        ->assertStatus(200)
        ->assertJsonFragment($data);
});

it('update a card with invalid id', function () {
    $data = [
        'name' => fake()->creditCardType(),
        'closing_date' => fake()->date(),
        'due_date' => fake()->date()
    ];

    actingAs($this->user)
        ->putJson("/api/cards/123", $data)
        ->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Card not found'
        ]);
});

it('delete a card', function () {
    $card = Card::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->deleteJson("/api/cards/{$card->id}")
        ->assertStatus(200)
        ->assertJsonFragment([
            'message' => 'Card deleted successfully'
        ]);
});

it('delete a card with invalid id', function () {
    actingAs($this->user)
        ->deleteJson("/api/cards/123")
        ->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Card not found'
        ]);
});
