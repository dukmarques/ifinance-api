<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\CreateCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoriesController extends BaseController
{
    public function __construct(CategoryService $service) {
        $this->service = $service;
        $this->storeFormRequest = CreateCategoryRequest::class;
        $this->updateFormRequest = UpdateCategoryRequest::class;
    }
}
