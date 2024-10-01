<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditCard\CreateCreditCardRequest;
use App\Http\Requests\CreditCard\UpdateCreditCardRequest;
use App\Services\CardService;
use Illuminate\Http\Request;

class CardsController extends BaseController
{
    public function __construct(CardService $service)
    {
        $this->service = $service;
        $this->storeFormRequest = CreateCreditCardRequest::class;
        $this->updateFormRequest = UpdateCreditCardRequest::class;
    }
}
