<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Card;
use App\Models\ExpenseAssignees;
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

    it('create a simple expense as non-owner persisting owner', function () {
        $assignee = ExpenseAssignees::factory()->createOne([
            'user_id' => $this->user->id,
        ]);

        $expenseData = [
            ...$this->expenseData,
            'is_owner' => false,
            'assignee_id' => $assignee->id,
            'owner' => fake()->name(),
        ];

        $response = actingAs($this->user)
            ->postJson('/api/expenses', $expenseData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'is_owner' => false,
                'owner' => $expenseData['owner'],
            ]);

        $expense = Expenses::query()->find($response->json('id'));

        expect($expense)->not->toBeNull()
            ->and($expense->owner)->toBe($expenseData['owner']);
    });

    it('create a simple expense as non-owner with assignee from another user', function () {
        $assignee = ExpenseAssignees::factory()->createOne([
            'user_id' => User::factory()->createOne()->id,
        ]);

        $expenseData = [
            ...$this->expenseData,
            'is_owner' => false,
            'assignee_id' => $assignee->id,
            'owner' => fake()->name(),
        ];

        actingAs($this->user)
            ->postJson('/api/expenses', $expenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['assignee_id']);
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

describe('expenses listing and show', function () {
    it('get expenses for selected month', function () {
        $recurrentExpense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => Carbon::now()->subMonths(2)->toDateString(),
            'deprecated_date' => null,
            'user_id' => $this->user->id,
        ]);

        $notRecurrentExpense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => false,
            'payment_month' => Carbon::now()->toDateString(),
            'user_id' => $this->user->id,
        ]);

        Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => false,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'user_id' => $this->user->id,
        ]);

        Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => Carbon::now()->addMonth()->toDateString(),
            'deprecated_date' => null,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->getJson("/api/expenses?date={$this->date->toDateString()}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $recurrentExpense->id,
                'recurrent' => true,
            ])
            ->assertJsonFragment([
                'id' => $notRecurrentExpense->id,
                'recurrent' => false,
            ]);
    });

    it('does not list expenses from another user', function () {
        $anotherUser = User::factory()->createOne();

        $currentUserExpense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => false,
            'payment_month' => Carbon::now()->toDateString(),
            'user_id' => $this->user->id,
        ]);

        $expenseFromAnotherUser = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => false,
            'payment_month' => Carbon::now()->toDateString(),
            'user_id' => $anotherUser->id,
        ]);

        actingAs($this->user)
            ->getJson("/api/expenses?date={$this->date->toDateString()}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $currentUserExpense->id,
            ])
            ->assertJsonMissing([
                'id' => $expenseFromAnotherUser->id,
            ]);
    });

    it('get an expense by id', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->getJson("/api/expenses/{$expense->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $expense->id,
                'title' => $expense->title,
                'amount' => currency_format($expense->amount),
                'user_id' => $this->user->id,
            ]);
    });

    it('get a non-existent expense', function () {
        actingAs($this->user)
            ->getJson('/api/expenses/' . fake()->uuid())
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Resource not found',
            ]);
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

    it('update a recurrent expense only current month does not duplicate override', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->subMonths(5)->toDateString(),
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        $paymentMonth = Carbon::now()->addMonth()->toDateString();

        $firstUpdate = [
            'title' => 'Internet',
            'amount' => 10000,
            'is_owner' => true,
            'paid' => false,
            'payment_month' => $paymentMonth,
            'description' => 'first override',
            'update_type' => Expenses::EDIT_TYPE_ONLY_MONTH,
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $firstUpdate)
            ->assertStatus(Response::HTTP_OK);

        $secondUpdate = [
            ...$firstUpdate,
            'title' => 'Internet updated',
            'amount' => 13000,
            'paid' => true,
            'description' => 'second override',
        ];

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", $secondUpdate)
            ->assertStatus(Response::HTTP_OK);

        $overrides = ExpensesOverride::query()
            ->where('expense_id', $expense->id)
            ->whereDate('payment_month', $paymentMonth)
            ->get();

        expect($overrides->count())->toBe(1)
            ->and($overrides->first()->title)->toBe('Internet updated')
            ->and($overrides->first()->amount)->toBe(13000)
            ->and((bool) $overrides->first()->paid)->toBeTrue();
    });

    it('cannot update recurrent expense without update type', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->putJson("/api/expenses/{$expense->id}", [
                'title' => fake()->text(20),
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['update_type']);
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

    it('update recurrent expense payment status without date', function () {
        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'payment_month' => Carbon::now()->toDateString(),
            'recurrent' => true,
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
                'override' => [
                    'paid' => true,
                ]
            ]);
    });

    it('delete recurrent expense only in selected month creating an override', function () {
        $date = Carbon::now()->addMonth()->toDateString();

        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => Carbon::now()->subMonths(4)->toDateString(),
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}", [
                'delete_type' => Expenses::DELETE_TYPE_ONLY_MONTH,
                'date' => $date,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $override = ExpensesOverride::query()
            ->where('expense_id', $expense->id)
            ->whereDate('payment_month', $date)
            ->first();

        expect($override)->not->toBeNull()
            ->and((bool) $override->is_deleted)->toBeTrue()
            ->and(Expenses::query()->find($expense->id))->not->toBeNull();
    });

    it('delete recurrent expense only in selected month updating existing override', function () {
        $date = Carbon::now()->addMonth()->toDateString();

        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => Carbon::now()->subMonths(4)->toDateString(),
            'user_id' => $this->user->id,
        ]);

        $override = ExpensesOverride::query()->create([
            'expense_id' => $expense->id,
            'payment_month' => $date,
            'is_deleted' => false,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}", [
                'delete_type' => Expenses::DELETE_TYPE_ONLY_MONTH,
                'date' => $date,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(ExpensesOverride::query()
            ->where('expense_id', $expense->id)
            ->whereDate('payment_month', $date)
            ->count())
            ->toBe(1)
            ->and((bool) $override->refresh()->is_deleted)->toBeTrue();
    });

    it('delete recurrent expense in current and future months', function () {
        $date = Carbon::now()->addMonth()->toDateString();

        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => Carbon::now()->subMonths(6)->toDateString(),
            'deprecated_date' => null,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}", [
                'delete_type' => Expenses::DELETE_TYPE_CURRENT_AND_FUTURE,
                'date' => $date,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $updatedExpense = Expenses::query()->find($expense->id);

        expect($updatedExpense)->not->toBeNull()
            ->and($updatedExpense->deprecated_date)->toBe(
                Carbon::parse($date)->subMonth()->toDateString()
            );
    });

    it('delete recurrent expense in current and future months in start month removes all', function () {
        $paymentMonth = Carbon::now()->toDateString();

        $expense = Expenses::factory()->createOne([
            ...$this->expenseData,
            'recurrent' => true,
            'payment_month' => $paymentMonth,
            'deprecated_date' => null,
            'user_id' => $this->user->id,
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expenses/{$expense->id}", [
                'delete_type' => Expenses::DELETE_TYPE_CURRENT_AND_FUTURE,
                'date' => $paymentMonth,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(Expenses::query()->find($expense->id))->toBeNull();
    });
});
