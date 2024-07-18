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
use Illuminate\Database\Eloquent\Factories\Sequence;

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

describe('update credit card expense installment', function () {
    beforeEach(function () {
        $totalAmount = fake()->randomNumber(5, true);
        $paymentMonth = Carbon::now();

        $this->cardExpense = CardExpenses::factory()
            ->has(
                CardInstallments::factory()
                    ->count(12)
                    ->state(new Sequence(
                        function ($sequence) use ($totalAmount, $paymentMonth) {
                            return [
                                'amount' => $totalAmount / 12,
                                'payment_month' => $paymentMonth->copy()->addMonths($sequence->index),
                                'installment_number' => $sequence->index + 1,
                            ];
                        }
                    )),
                'installments'
            )
            ->create([
                'total_amount' => $totalAmount,
                'user_id' => $this->user->id,
                'card_id' => $this->card->id,
                'category_id' => $this->category->id,
            ]);

        $this->url = "api/card-expenses/{$this->cardExpense->id}/installments";
        $this->updateData = [
            'title' => fake()->text(20),
            'amount' => fake()->randomNumber(4, true),
            'paid' => fake()->boolean(),
            'notes' => fake()->text(300),
        ];
    });

    it('every month', function () {
        $installment = $this->cardExpense->installments[0];
        $this->updateData['update_type'] = CardInstallments::EDIT_TYPE_ALL;

        actingAs($this->user)
            ->putJson("{$this->url}/{$installment->id}", $this->updateData)
            ->assertStatus(Response::HTTP_OK);

        $updateData = collect($this->updateData)->except('update_type')->toArray();
        $twoInstallments = $this->cardExpense->installments[1];
        expect(collect($installment->refresh())->only(['title', 'amount', 'paid', 'notes'])->toArray())
            ->toBe($updateData)
            ->and(collect($twoInstallments->refresh())->only(['title', 'amount', 'paid', 'notes'])->toArray())
            ->toBe($updateData);
    });

    it('current month only', function () {
        $this->updateData['update_type'] = CardInstallments::EDIT_TYPE_ONLY_MONTH;
        $installment = $this->cardExpense->installments[fake()->numberBetween(0, 11)];

        actingAs($this->user)
            ->putJson("{$this->url}/{$installment->id}", $this->updateData)
            ->assertStatus(Response::HTTP_OK);

        $updateData = collect($this->updateData)->except('update_type')->toArray();
        $installment = collect($installment->refresh())->only(['title', 'amount', 'paid', 'notes'])->toArray();
        expect($installment)->toBe($updateData);
    });

    it('current and upcoming months', function () {
        $this->updateData['update_type'] = CardInstallments::EDIT_TYPE_CURRENT_AND_FUTURE;
        $installment = $this->cardExpense->installments[2];

        actingAs($this->user)
            ->putJson("{$this->url}/{$installment->id}", $this->updateData)
            ->assertStatus(Response::HTTP_OK);

        $updateData = collect($this->updateData)->except('update_type')->toArray();
        $installmentNotUpdated = collect($this->cardExpense->installments[0])
            ->only(['title', 'amount', 'paid', 'notes'])
            ->toArray();
        $lastInstallment = collect($this->cardExpense->installments[11]->refresh())
            ->only(['title', 'amount', 'paid', 'notes'])
            ->toArray();

        expect(collect($installment->refresh())->only(['title', 'amount', 'paid', 'notes'])->toArray())
            ->toBe($updateData)
            ->and($installmentNotUpdated)->not()->toBe($updateData)
            ->and($lastInstallment)->toBe($updateData);
    });
});

describe('delete credit card expense installment', function (){
    beforeEach(function () {
        $totalAmount = fake()->randomNumber(5, true);
        $paymentMonth = Carbon::now();

        $this->cardExpense = CardExpenses::factory()
            ->has(
                CardInstallments::factory()
                    ->count(12)
                    ->state(new Sequence(
                        function ($sequence) use ($totalAmount, $paymentMonth) {
                            return [
                                'amount' => $totalAmount / 12,
                                'payment_month' => $paymentMonth->copy()->addMonths($sequence->index),
                                'installment_number' => $sequence->index + 1,
                            ];
                        }
                    )),
                'installments'
            )
            ->create([
                'total_amount' => $totalAmount,
                'user_id' => $this->user->id,
                'card_id' => $this->card->id,
                'category_id' => $this->category->id,
            ]);

        $this->url = "api/card-expenses/{$this->cardExpense->id}/installments";
    });

    it('all installments', function () {
        $installment = $this->cardExpense->installments[0];

        actingAs($this->user)
            ->deleteJson("{$this->url}/{$installment->id}", ['delete_type' => CardInstallments::EDIT_TYPE_ALL])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Expense successfully removed']);

        expect(CardExpenses::query()->find($this->cardExpense->id))->toBe(null)
            ->and(CardInstallments::query()->count())->toBe(0);
    });

    it('current month only', function () {
        $installment = $this->cardExpense->installments[fake()->numberBetween(0, 11)];

        actingAs($this->user)
            ->deleteJson("{$this->url}/{$installment->id}", ['delete_type' => CardInstallments::EDIT_TYPE_ONLY_MONTH])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Expense installment successfully removed']);

        expect(CardInstallments::query()->find($installment->id))->toBe(null);
    });

    it('current and upcoming months', function () {
        $installment = $this->cardExpense->installments[1];

        actingAs($this->user)
            ->deleteJson("{$this->url}/{$installment->id}", ['delete_type' => CardInstallments::EDIT_TYPE_CURRENT_AND_FUTURE])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Current and upcoming installments successfully deleted']);

        expect(CardInstallments::query()->find($installment->id))->toBe(null)
            ->and(CardInstallments::query()->count())->toBe(1);
    });
});
