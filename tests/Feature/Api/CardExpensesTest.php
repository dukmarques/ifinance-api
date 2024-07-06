<?php

use Carbon\Carbon;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use App\Models\Card;
use App\Models\CardExpenses;
use App\Models\CardInstallments;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->url = '/api/card-expenses';
    $this->user = User::factory()->createOne();
    $this->category = Category::factory()->createOne([
        'user_id' => $this->user->id
    ]);
    $this->card = Card::factory()->createOne([
        'user_id' => $this->user->id,
    ]);
    $this->date = Carbon::now();
    $this->cardExpenseData = [
        'title' => fake()->text(20),
        'total_amount' => fake()->randomNumber(5, true),
        'is_owner' => true,
        'category_id' => $this->category->id ?: null,
        'card_id' => $this->card->id ?: null,
        'initial_installment' => 1,
        'final_installment' => 12,
        'date' => Carbon::now(),
    ];
});

describe('create card expense', function () {
    it('create a card expense', function () {
        actingAs($this->user)
            ->postJson($this->url, $this->cardExpenseData)
            ->assertStatus(Response::HTTP_CREATED);

        expect(CardExpenses::query()->count())->toBe(1)
            ->and(CardInstallments::query()->count())->toBe($this->cardExpenseData['final_installment']);
    });

    it('create a card expense with initial installment greater than final installment', function () {
        $this->cardExpenseData['initial_installment'] = 10;
        $this->cardExpenseData['final_installment'] = 8;

        actingAs($this->user)
            ->postJson($this->url, $this->cardExpenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The final installment field must be greater than or equal to 10.'
            ]);
    });

    it('create a card expense without initial installment', function () {
        $this->cardExpenseData['initial_installment'] = null;

        actingAs($this->user)
            ->postJson($this->url, $this->cardExpenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The initial installment field is required. (and 1 more error)'
            ]);
    });

    it('create a card expense without final installment', function () {
        $this->cardExpenseData['final_installment'] = null;

        actingAs($this->user)
            ->postJson($this->url, $this->cardExpenseData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The final installment field is required.'
            ]);
    });
});

describe('update card expense', function () {
    it('update a installment expense', function () {
    });
});
