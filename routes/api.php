<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('v1/auth/login', 'Api\AuthController@login')->name('auth.login');
Route::get('v1/auth/refresh', 'Api\AuthController@refresh')->name('auth.refresh');

Route::namespace('Api')->prefix('v1')->middleware(['refresh'])->group(function (){

    Route::prefix('auth')->group(function(){
        Route::get('me', 'AuthController@me')->name('auth.me');
    });

    # 角色路由
    Route::prefix('role')->group(function() {
        Route::post('store', 'RoleController@store')->name('role.store');
        Route::get('index', 'RoleController@index')->name('role.index');
        Route::post('update', 'RoleController@update')->name('role.update');
        Route::delete('delete', 'RoleController@delete')->name('role.delete');
    });

    # 用户路由
    Route::prefix('user')->group(function() {
        Route::post('store', 'UserController@store')->name('user.store');
        Route::get('index', 'UserController@index')->name('user.index');
        Route::delete('delete', 'UserController@delete')->name('user.delete');
        Route::post('status', 'UserController@status')->name('user.status');
        Route::post('reset_password', 'UserController@reset_password')->name('user.reset_password');
        Route::get('detail', 'UserController@detail')->name('user.detail');
        Route::post('update', 'UserController@update')->name('user.update');
        Route::get('tag_data', 'UserController@tag_data')->name('user.tag_data');
    });

    # 菜单路由
    Route::prefix('menu')->group(function() {
        Route::post('store', 'MenuController@store')->name('menu.store');
        Route::get('index', 'MenuController@index')->name('menu.index');
        Route::delete('delete', 'MenuController@delete')->name('menu.delete');
        Route::get('select', 'MenuController@getSelectList')->name('menu.select');
        Route::get('detail', 'MenuController@detail')->name('menu.detail');
        Route::post('update', 'MenuController@update')->name('menu.update');
        Route::get('tree', 'MenuController@tree')->name('menu.tree');
        Route::get('user_menu', 'MenuController@user_menu')->name('menu.user_menu');
        Route::get('element', 'MenuController@element')->name('menu.element');
    });

    # 角色菜单路由
    Route::prefix('role_menu')->group(function() {
        Route::post('store', 'RoleMenuController@store')->name('role_menu.store');
        Route::get('index', 'RoleMenuController@index')->name('role_menu.index');
    });

    # 权限路由
    Route::prefix('perm')->group(function(){
        Route::post('store', 'PermController@store')->name('perm.store');
        Route::get('select', 'PermController@getSelectList')->name('perm.select');
        Route::get('index', 'PermController@index')->name('perm.index');
        Route::delete('delete', 'PermController@delete')->name('perm.delete');
        Route::get('detail', 'PermController@detail')->name('perm.detail');
        Route::post('update', 'PermController@update')->name('perm.update');
        Route::get('tree', 'PermController@tree')->name('perm.tree');

        Route::get('test', 'PermController@test');
    });

    # 角色权限路由
    Route::prefix('role_perm')->group(function(){
        Route::get('index', 'RolePermController@index')->name('role_perm.index');
        Route::post('store', 'RolePermController@store')->name('role_perm.store');
    });

    # 配置路由
    Route::prefix('config')->group(function(){
        Route::post('store', 'ConfigController@store')->name('config.store');
        Route::get('index', 'ConfigController@index')->name('config.index');
        Route::get('detail', 'ConfigController@detail')->name('config.detail');
        Route::post('update', 'ConfigController@update')->name('config.update');

    });

    # 用户详情
    Route::prefix('info')->group(function() {
        Route::get('index', 'UserInfoController@index')->name('info.index');
        Route::get('detail', 'UserInfoController@detail')->name('info.detail');
        Route::post('update', 'UserInfoController@update')->name('info.update');
    });

    # 子账户路由
    Route::prefix('son')->group(function(){
        Route::post('store', 'SonController@store')->name('son.store');
        Route::get('index', 'SonController@index')->name('son.store');
        Route::get('detail', 'SonController@detail')->name('son.detail');
        Route::post('update', 'SonController@update')->name('son.update');
        Route::delete('delete', 'SonController@delete')->name('son.delete');
        Route::post('status', 'SonController@status')->name('son.status');
    });



});
