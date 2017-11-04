<?php
   
namespace Zhiyi\Component\ZhiyiPlus\PlusComponentH5\Controllers;

use Log;
use Illuminate\Http\Request;
use Zhiyi\Plus\Http\Controllers\Controller;

class WechatController extends Controller
{
    /**
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.');

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "welcome to here";
        });

        Log::info('return response.');

        return $wechat->server->server();
    }
}
