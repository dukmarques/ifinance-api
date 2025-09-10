<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Card;
use App\Models\Expenses;
use App\Models\ExpensesOverride;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Faker\fake;
use function Pest\Laravel\{postJson, actingAs};

beforeEach(function () {
    $this->user = User::factory()->createOne();
    $this->category = Category::factory()->createOne([
        'user_id' => $this->user->id
    ]);

    $this->date = Carbon::now();
    $this->expenseData = [
        'title' => fake()->text(20),
        'recurrent' => false,
        'amount' => fake()->randomNumber(5, true),
        'is_owner' => true,
        'paid' => false,
        'payment_month' => Carbon::now()->toDateString(),
        'description' => fake()->text(300),
        'category_id' => $this->category->id ?: null,
    ];
});

describe('simple expense', function () {
    it('create a simple expense', function () {
        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'amount' => ($this->expenseData['amount'] / 100),
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
        $this->expenseData['amount'] = null;

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect(Expenses::query()->count())->toBe(0);
    });

    it('update a simple expense', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'amount' => fake()->randomNumber(5, true),
            'recurrent' => false,
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
                'amount' => ($updatedExpense['amount'] / 100),
            ]);
    });

    it('update expense payment status', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'paid' => false,
            'user_id' => $this->user->id,
        ]);

        $payload = [
            'paid' => true,
        ];

        actingAs($this->user)
            ->postJson("/api/expenses/{$expense->id}/update-expense-payment-status", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $expense->id,
                'paid' => true,
            ]);
    });

    it('delete a simple expense', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}", [
                'delete_type' => Expenses::DELETE_TYPE_ALL,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(Expenses::query()->find($expense->id))->toBeNull();
    });

    it('delete a simple expense without delete  type', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'The delete type field is required.']);
    });
});

describe('recurrent expense', function () {
    beforeEach(function () {
        $this->expenseData['recurrent'] = true;
    });

    it('create a recurrent expense', function () {
        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'amount' => ($this->expenseData['amount'] / 100),
            ]);

        expect(Expenses::query()->count())->toBe(1);
    });

    it('create a recurrent expense with deprecated month', function () {
        $this->expenseData['deprecated_date'] = Carbon::now()->addMonths(3)->toDateString();

        actingAs($this->user)
            ->postJson('/api/expenses', $this->expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'amount' => ($this->expenseData['amount'] / 100),
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
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'update_type' => Expenses::EDIT_TYPE_ALL,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                ...collect($updatedExpense)->except('update_type')->toArray(),
                'amount' => ($updatedExpense['amount'] / 100),
            ]);
    });

    it('update a recurring expense in current month e future', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->subMonths(5)->toDateString(),
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'update_type' => Expenses::EDIT_TYPE_CURRENT_AND_FUTURE,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                ...collect($updatedExpense)
                    ->except(['update_type', 'recurrence_update_date'])
                    ->toArray(),
                'amount' => ($updatedExpense['amount'] / 100),
                'deprecated_date' => null,
            ]);
    });

    it('update a recurrent expense only current month', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->subMonths(5)->toDateString(),
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        $updatedExpense = [
            'title' => fake()->text(20),
            'amount' => fake()->randomNumber(5, true),
            'is_owner' => false,
            'paid' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'description' => fake()->text(300),
            'update_type' => Expenses::EDIT_TYPE_ONLY_MONTH,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $updatedExpense)
            ->assertStatus(Response::HTTP_OK);

        expect(ExpensesOverride::query()->count())->toBe(1);
    });

    it('update expense payment status', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->toDateString(),
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        $payload = [
            'paid' => true,
            'date' => Carbon::now()->toDateString(),
        ];

        actingAs($this->user)
            ->postJson("/api/expenses/{$expense->id}/update-expense-payment-status", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $expense->id,
                'override' => [
                    'paid' => true,
                ]
            ]);
    });
});
