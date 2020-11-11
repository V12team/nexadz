<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('app');
});




Route::get('/customers', 'CustomerController@index');
Route::get('/customers/dump', 'CustomerController@dump');
//facebook creation compaign routes
Route::get('/create-new-compaign', 'FacebookCampaign@createNewCompaign');
Route::post('/create-new-compaign', 'FacebookCampaign@saveCompaign');

// reports routes
Route::get('/all-performance', 'CampaignReport@index');
Route::get('/weekly-performance', 'CampaignReport@weeklyPerformance');
Route::get('/monthly-performance', 'CampaignReport@monthlyPerformance');
Route::get('/all-active-customers', 'CampaignReport@allActiveCustomers');





Route::group(['prefix' => 'customer'], function () {
    Route::get('/{id}', 'CustomerController@show');
});

Route::get('/adaccount/create/{id}', 'AdController@createAdAccount');

// Webhooks
Route::group(['middleware' => \App\Http\Middleware\secureWebhook::class], function () {
    Route::group(['prefix' => 'zoho'], function () {
        Route::post('/balance/update', 'CustomerController@updateBalance');
    });
});
