<?php

namespace App\Services;

use App\Http\Resources\CardInstallmentsResource;
use App\Models\CardInstallments;

class CardInstallmentsService
{
    public function update($installmentId, $updateData) {
        $installment = CardInstallments::query()->findOrFail($installmentId);
        $cardExpense = $installment->expense;

        $updateType = $updateData['update_type'];
        $data = collect($updateData)->except(['update_type'])->toArray();

        if ($updateType === CardInstallments::EDIT_TYPE_ALL) {
            $cardExpense->installments()->update($data);
        }

        if ($updateType === CardInstallments::EDIT_TYPE_CURRENT_AND_FUTURE) {
            $cardExpense->installments()
                ->where('installment_number', '>=', $installment->installment_number)
                ->update($data);
        }

        if ($updateType === CardInstallments::EDIT_TYPE_ONLY_MONTH) {
            $installment->update($data);
        }

        return new CardInstallmentsResource($installment->refresh());
    }
}
