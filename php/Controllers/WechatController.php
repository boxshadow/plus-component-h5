<?php
   
namespace Zhiyi\Component\ZhiyiPlus\PlusComponentH5\Controllers;

use Illuminate\Http\Request;

use Log;

class WechatController extends Controller
{
    /**
     *
     * @return string
     */
    public function serve()
    {
        // Log::info('request arrived.');

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "welcome to here";
        });
        return $wechat;
        Log::info('return response.');

        return $wechat->server->server();
    }
}
