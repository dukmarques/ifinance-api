<?php

namespace App\Services;

use App\Http\Resources\CardResource;
use App\Models\Card;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = Card::class;
        $this->resourceClass = CardResource::class;
    }
}
