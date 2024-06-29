<?php

use Carbon\Carbon;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use \App\Models\Card;
use \App\Models\Expenses;
use \App\Models\ExpenseInstallments;
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

describe('simple expense', function () {
    it('create a simple expense', function () {
        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                ...$this->expenseData,
                'total_amount' => ($this->expenseData['total_amount'] / 100),
            ]);

        expect(Expenses::query()->count())->toBe(1);
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

        expect(Expenses::query()->count())->toBe(0);
    });
});

describe('recurrent expesne', function () {
    beforeEach(function () {
        $this->expenseData['type'] = 'recurrent';
    });

    it('create a recurrent expense', function () {
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

    it('creates a recurring expense with the payment month greater than the deprecated month', function () {
        $this->expenseData['payment_month'] = Carbon::now()->addMonth()->toDateString();
        $this->expenseData['deprecated_date'] = Carbon::now()->toDateString();

        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The payment month must not be greater than the deprecated month.',
            ]);
    });
});

describe('installments expenses', function () {
    beforeEach(function (){
        $this->expenseData['type'] = 'installments';
        $this->expenseData['initial_installment'] = 1;
        $this->expenseData['final_installment'] = 12;
        $this->expenseData['installment_amount'] = ($this->expenseData['total_amount'] / $this->expenseData['final_installment']) * 100;
    });

    it('create a installment expense', function () {
        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
        $response->assertStatus(Response::HTTP_CREATED);

        expect(Expenses::query()->count())->toBe(1)
            ->and(ExpenseInstallments::query()->count())->toBe($this->expenseData['final_installment']);
    });

    it('create a installment expense with initial installment greater than final installment', function () {
        $this->expenseData['initial_installment'] = 10;
        $this->expenseData['final_installment'] = 8;
        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The final installment field must be greater than 10.'
            ]);
    });

    it('create a installment expense without initial installment', function () {
        $this->expenseData['initial_installment'] = null;

        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The initial installment field must have a value. (and 1 more error)'
            ]);
    });

    it('create a installment expense without final installment', function () {
        $this->expenseData['final_installment'] = null;

        $response = $this->actingAs($this->user)->postJson('/api/expenses', $this->expenseData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The final installment field must have a value.'
            ]);
    });
});
