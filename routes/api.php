<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
  // User
  Route::get('/users/current', [UserController::class, 'get']);
  Route::patch('/users/current', [UserController::class, 'update']);
  Route::delete('/users/logout', [UserController::class, 'logout']);

  // Contact
  Route::post('/contacts', [ContactController::class, 'create']);
  Route::get('/contacts', [ContactController::class, 'search']);
  Route::get('/contacts/{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/contacts/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/contacts/{id}', [ContactController::class, 'delete'])->where('id', '[0-9]+');

  // Address
  Route::post('/contacts/{contactId}/addresses', [AddressController::class, 'create'])->where('contactId', '[0-9]+');
  Route::prefix('/contacts/{contactId}/addresses/{addressId}')
    ->where([
      'contactId' => '[0-9]+',
      'addressId' => '[0-9]+',
    ])->group(function () {
      Route::get('/', [AddressController::class, 'get']);
      Route::put('/', [AddressController::class, 'update']);
      Route::delete('/', [AddressController::class, 'delete']);
    });
});
