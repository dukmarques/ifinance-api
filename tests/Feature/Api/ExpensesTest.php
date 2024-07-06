<?php

use Carbon\Carbon;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use \App\Models\Card;
use \App\Models\Expenses;
use \App\Models\ExpenseInstallments;
use \App\Models\ExpensesOverride;
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
        'category_id' => $this->category->id ?: null,
        'card_id' => $this->card->id ?: null,
    ];
});

describe('simple expense', function () {
    it('create a simple expense', function () {
        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'total_amount' => ($this->expenseData['total_amount'] / 100),
            ]);
    });

    it('create a simple expense without login', function () {
        postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });

    it('create a simple expense without required attributes', function () {
        $this->expenseData['title'] = null;
        $this->expenseData['type'] = 'credit';
        $this->expenseData['total_amount'] = null;

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The title field is required. (and 2 more errors)',
            ]);

        expect(Expenses::query()->count())->toBe(0);
    });

    it('update a simple expense', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                ...$updatedExpense,
                'total_amount' => ($updatedExpense['total_amount'] / 100),
            ]);
    });
});

describe('recurrent expense', function () {
    beforeEach(function () {
        $this->expenseData['type'] = 'recurrent';
    });

    it('create a recurrent expense', function () {
        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'total_amount' => ($this->expenseData['total_amount'] / 100),
            ]);

        expect(Expenses::query()->count())->toBe(1);
    });

    it( 'create a recurrent expense with deprecated month', function () {
        $this->expenseData['deprecated_date'] = Carbon::now()->addMonths(3)->toDateString();

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'total_amount' => ($this->expenseData['total_amount'] / 100),
            ]);

        expect(Expenses::query()->count())->toBe(1);
    });

    it('create a recurrent expense with same payment and deprecated month', function () {
        $this->expenseData['deprecated_date'] = Carbon::now()->toDateString();

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The deprecated date must be a date after the payment month.',
            ]);
    });

    it('creates a recurring expense with the payment month greater than the deprecated month', function () {
        $this->expenseData['payment_month'] = Carbon::now()->addMonth()->toDateString();
        $this->expenseData['deprecated_date'] = Carbon::now()->toDateString();

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The payment month must not be greater than the deprecated month.',
            ]);
    });

    it('update a recurring expense in all months', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'type' => Expenses::TYPE_RECURRENT,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'recurrence_update_type' => Expenses::EDIT_TYPE_ALL,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                ...collect($updatedExpense)->except('recurrence_update_type')->toArray(),
                'total_amount' => ($updatedExpense['total_amount'] / 100),
            ]);
    });

    it('update a recurring expense in current month e future', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->subMonths(5)->toDateString(),
            'type' => Expenses::TYPE_RECURRENT,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'recurrence_update_type' => Expenses::EDIT_TYPE_CURRENT_AND_FUTURE,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                ...collect($updatedExpense)
                    ->except(['recurrence_update_type', 'recurrence_update_date'])
                    ->toArray(),
                'total_amount' => ($updatedExpense['total_amount'] / 100),
                'deprecated_date' => null,
            ]);
    });

    it('update a recurrent expense only current month', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->subMonths(5)->toDateString(),
            'type' => Expenses::TYPE_RECURRENT,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'recurrence_update_type' => Expenses::EDIT_TYPE_ONLY_MONTH,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK);

        expect(ExpensesOverride::query()->count())->toBe(1);
    });
});
