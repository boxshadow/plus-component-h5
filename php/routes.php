<?php

use Zhiyi\Plus\Http\Middleware;
use Zhiyi\Component\ZhiyiPlus\PlusComponentH5\Controllers\WechatController;

Route::namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentH5\\Controllers')
    ->prefix('/web')
    ->group(function () {
        Route::get('/{route?}', 'HomeController@index')
        	->where('route', '.*')
            ->name('H5');
    });

Route::namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentH5\\Controllers')
    ->prefix('/h5')
    ->group(function () {
        Route::get('/{route?}', 'HomeController@index')
        	->where('route', '.*')
            ->name('H5');
    });

Route::any('/wechat', WechatController::class. '@serve');

    
Route::namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentH5\\Controllers')
    ->group(['middleware' => ['web', 'wechat.oauth']], function () {
    Route::get('/wechatuser', function () {
        
        return redirect()->route('H5');
    });
});
