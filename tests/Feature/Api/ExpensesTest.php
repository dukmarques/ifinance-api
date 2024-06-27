<?php

use Carbon\Carbon;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use \App\Models\Card;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->user = User::factory()->createOne();
    $this->category = Category::factory()->createOne([
        'user_id' => $this->user->id
    ]);
    $this->card = Card::factory()->createOne([
        'user_id' => $this->user->id,
    ]);
    $this->date = Carbon::now();
    $this->expenseData = [
        'title' => fake()->text(20),
        'type' => 'simple',
        'total_amount' => fake()->randomNumber(5, true),
        'is_owner' => true,
        'paid' => false,
        'payment_month' => Carbon::now()->toDateString(),
        'description' => fake()->text(300),
        'category_id' => $this->category->id,
        'card_id' => $this->card->id,
    ];
});

it('create a simple expense', function () {
    $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            ...$this->expenseData,
            'total_amount' => ($this->expenseData['total_amount'] / 100),
        ]);
});

it('create a simple expense without required attributes', function () {
    $this->expenseData['title'] = null;
    $this->expenseData['type'] = 'credit';
    $this->expenseData['total_amount'] = null;

    $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'message' => 'The title field is required. (and 2 more errors)',
        ]);
});

it('create a recurrent expense', function () {
    $this->expenseData['type'] = 'recurrent';
    $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            ...$this->expenseData,
            'total_amount' => ($this->expenseData['total_amount'] / 100),
        ]);
});

it( 'create a recurrent expense with deprecated month', function () {
    $this->expenseData['deprecated_date'] = Carbon::now()->addMonths(3)->toDateString();
    $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            ...$this->expenseData,
            'total_amount' => ($this->expenseData['total_amount'] / 100),
        ]);
});

it('create a recurrent expense with same payment and deprecated month', function () {
    $this->expenseData['deprecated_date'] = Carbon::now()->toDateString();
    $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'message' => 'The deprecated date must be a date after the payment month.',
        ]);
});
