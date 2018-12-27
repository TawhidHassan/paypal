<?php

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
    return view('welcome');
});
Route::get('/execute-payment','PaymentController@execute');
Route::post('/create-payment','PaymentController@create')->name('create-payment');
Route::get('/execute-agreement','PaymentController@executed');
Route::get('/listPlan','PaymentController@listPlan');
Route::get('/planDetails/{id}','PaymentController@planDetails');
Route::get('/activePlan/{id}','PaymentController@activePlan');
Route::post('/agrement/{id}','PaymentController@agrement')->name('agrement');
Route::get('/execute-agreement/{success}','PaymentController@executeagrement');
