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

Route::post('v1/auth/login', 'Api\AuthController@login')->name('auth.login');
Route::get('v1/auth/refresh', 'Api\AuthController@refresh')->name('auth.refresh');

Route::namespace('Api')->prefix('v1')->middleware(['refresh'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('me', 'AuthController@me')->name('auth.me');
    });

    # 角色路由
    Route::prefix('role')->group(function () {
        Route::post('store', 'RoleController@store')->name('role.store');
        Route::get('index', 'RoleController@index')->name('role.index');
        Route::post('update', 'RoleController@update')->name('role.update');
        Route::delete('delete', 'RoleController@delete')->name('role.delete');
    });

    # 用户路由
    Route::prefix('user')->group(function () {
        Route::post('store', 'UserController@store')->name('user.store');
        Route::get('index', 'UserController@index')->name('user.index');
        Route::delete('delete', 'UserController@delete')->name('user.delete');
        Route::post('status', 'UserController@status')->name('user.status');
        Route::post('reset_password', 'UserController@reset_password')->name('user.reset_password');
        Route::get('detail', 'UserController@detail')->name('user.detail');
        Route::post('update', 'UserController@update')->name('user.update');
        Route::get('tag_data', 'UserController@tag_data')->name('user.tag_data');
        Route::get('select', 'UserController@select')->name('user.select');
    });

    # 菜单路由
    Route::prefix('menu')->group(function () {
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
    Route::prefix('role_menu')->group(function () {
        Route::post('store', 'RoleMenuController@store')->name('role_menu.store');
        Route::get('index', 'RoleMenuController@index')->name('role_menu.index');
    });

    # 权限路由
    Route::prefix('perm')->group(function () {
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
    Route::prefix('role_perm')->group(function () {
        Route::get('index', 'RolePermController@index')->name('role_perm.index');
        Route::post('store', 'RolePermController@store')->name('role_perm.store');
    });

    # 配置路由
    Route::prefix('config')->group(function () {
        Route::post('store', 'ConfigController@store')->name('config.store');
        Route::get('index', 'ConfigController@index')->name('config.index');
        Route::get('detail', 'ConfigController@detail')->name('config.detail');
        Route::post('update', 'ConfigController@update')->name('config.update');

    });

    # 用户详情
    Route::prefix('info')->group(function () {
        Route::get('index', 'UserInfoController@index')->name('info.index');
        Route::get('detail', 'UserInfoController@detail')->name('info.detail');
        Route::post('update', 'UserInfoController@update')->name('info.update');
    });

    # 子账户路由
    Route::prefix('son')->group(function () {
        Route::post('store', 'SonController@store')->name('son.store');
        Route::get('index', 'SonController@index')->name('son.store');
        Route::get('detail', 'SonController@detail')->name('son.detail');
        Route::post('update', 'SonController@update')->name('son.update');
        Route::delete('delete', 'SonController@delete')->name('son.delete');
        Route::post('status', 'SonController@status')->name('son.status');
    });

    # 游戏路由
    Route::prefix('game')->group(function () {
        Route::post('store', 'GameController@store')->name('game.store');
        Route::get('index', 'GameController@index')->name('game.index');
        Route::delete('delete', 'GameController@delete')->name('game.delete');
        Route::get('detail', 'GameController@detail')->name('game.detail');
        Route::post('update', 'GameController@update')->name('game.update');
        Route::post('status', 'GameController@status')->name('game.status');
        Route::get('select', 'GameController@select')->name('game.select');
    });

    # 面值路由
    Route::prefix('price')->group(function () {
        Route::post('store', 'PriceController@store')->name('price.store');
        Route::get('index', 'PriceController@index')->name('price.index');
        Route::delete('delete', 'PriceController@delete')->name('price.delete');
        Route::get('detail', 'PriceController@detail')->name('price.detail');
        Route::post('update', 'PriceController@update')->name('price.update');
        Route::post('status', 'PriceController@status')->name('price.status');
        Route::post('pass', 'PriceController@pass')->name('price.pass');
        Route::get('select', 'PriceController@select')->name('price.select');
    });

    # 设备路由
    Route::prefix('device')->group(function () {
        Route::get('index', 'DeviceController@index')->name('device.index');
        Route::delete('delete', 'DeviceController@delete')->name('device.delete');
        Route::post('status', 'DeviceController@status')->name('device.status');
        Route::get('select', 'DeviceController@select')->name('device.select');
    });

    # 库存路由
    Route::prefix('stock')->group(function () {
        Route::get('index', 'StoreController@index')->name('stock.index');
        Route::get('detail', 'StoreController@detail')->name('stock.detail');
        Route::post('status', 'StoreController@status')->name('stock.status');
        Route::get('in_index', 'StoreController@in_index')->name('stock.in_index');
        Route::get('out_index', 'StoreController@out_index')->name('stock.out_index');
        Route::get('statistic', 'StoreController@statistic')->name('stock.statistic');
        Route::get('dist_index', 'StoreController@dist_index')->name('stock.dist_index');
        Route::post('dist', 'StoreController@dist')->name('stock.dist');
        Route::get('son_statistic', 'StoreController@son_statistic')->name('stock.son_statistic');
        Route::post('son_to_user', 'StoreController@son_to_user')->name('stock.son_to_user');

    });


});

Route::post('v1/port/auth/login', 'Port\AuthController@login');

Route::namespace('Port')->prefix('v1/port')->middleware([])->group(function () {
    Route::post('auth/logout', 'AuthController@logout');
    Route::post('game/price', 'GameController@price');
    Route::post('game/addGame', 'GameController@addGame');
    Route::post('game/support', 'GameController@support');
    Route::post('transaction/table', 'TransactionController@table');
    Route::post('transaction/desmise_input', 'TransactionController@desmise_input');
    Route::post('transaction/vendre_info_one', 'TransactionController@vendre_info_one');
    Route::post('transaction/vendre_info_one_moling', 'TransactionController@vendre_info_one_moling');
    Route::post('transaction/consumption', 'TransactionController@consumption');
    Route::post('transaction/invalid', 'TransactionController@invlid');

    Route::get('test', 'TestController.bak@test');
    Route::get('gtest', 'GameController@test');
});

