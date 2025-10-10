<?php

use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::post('organization', [OrganizationController::class, 'create']);
Route::get('/organization/show', [OrganizationController::class, 'show']);
