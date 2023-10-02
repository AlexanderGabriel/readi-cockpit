<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/admin', function () {
    if(Auth::hasRole('Administratoren')) {
        return view('index');
    } else {
        return abort(403);
    }
});


//Route::resource("groups", "App\Http\Controllers\GroupsController");

Route::group(['namespace' => 'App\Http\Controllers'], function()
{
    Route::group(['prefix' => 'groups'], function()
    {
        Route::group(['middleware' => 'keycloak-web'], function ()
        {
            Route::post('/create', 'GroupsController@store')->name('groups.store');
            Route::get('/create', 'GroupsController@create')->name('groups.create');
            Route::get('/{group}/edit', 'GroupsController@edit')->name('groups.edit');
            Route::patch('/{group}/update', 'GroupsController@update')->name('groups.update');
            Route::delete('/{group}/delete', 'GroupsController@destroy')->name('groups.destroy');
            Route::post('/{group}/addmember', 'GroupsController@addmember')->name('groups.addmember');
            Route::delete('/{group}/deletemember', 'GroupsController@deletemember')->name('groups.deletemember');
            Route::post('/{group}/toggleToBeInMailinglist', 'GroupsController@toggleToBeInMailinglist')->name('groups.toggleToBeInMailinglist');
            Route::post('/{group}/toggleToBeInNextCloud', 'GroupsController@toggleToBeInNextCloud')->name('groups.toggleToBeInNextCloud');
            Route::post('/{group}/toggleMembershipInKeycloak', 'GroupsController@toggleMembershipInKeycloak')->name('groups.toggleMembershipInKeycloak');
            Route::post('/{group}/toggleMembershipInKeycloakByEmail', 'GroupsController@toggleMembershipInKeycloakByEmail')->name('groups.toggleMembershipInKeycloakByEmail');
            Route::post('/{group}/toggleMembershipInMailman', 'GroupsController@toggleMembershipInMailman')->name('groups.toggleMembershipInMailman');
            Route::post('/{group}/toggleMembershipInMailmanByEmail', 'GroupsController@toggleMembershipInMailmanByEmail')->name('groups.toggleMembershipInMailmanByEmail');
            Route::post('/{group}/toggleToBeInGroup', 'GroupsController@toggleToBeInGroup')->name('groups.toggleToBeInGroup');
            Route::post('/{group}/allowJoin', 'GroupsController@allowJoin')->name('groups.allowJoin');
        });
        Route::get('/', 'GroupsController@index')->name('groups.index');
        Route::get('/{group}', 'GroupsController@show')->name('groups.show');
    });
});
