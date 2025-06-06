<?php

namespace plugin\wechat\app\controller;

use plugin\admin\app\model\Option;
use plugin\wechat\app\service\WechatService;

class ApiController
{
    public function push()
    {
        $app = WechatService::instance();
        $app->server->push(function ($message) {
            $reply = json_decode(Option::where('name', 'wechat_reply')->value('value'), true);
            if (isset($reply['mode']) && isset($reply['hook']) && in_array($reply['mode'], ['hook', 'mix']) && $ret = hook($reply['hook'], $message)) {
                return $ret;
            }
            if (!isset($reply['mode']) || in_array($reply['mode'], ['simple', 'mix'])) {
                if ($message['MsgType'] == 'event' && $message['Event'] == 'subscribe') {
                    return $reply['follow'] ?? '感谢关注我们';
                } elseif ($message['MsgType'] == 'text') {
                    foreach ($reply['rules'] as $key => $value) {
                        if ($message['Content'] == $key) {
                            return $value;
                        }
                    }
                    return $reply['default'] ?? '请点击菜单';
                }
            }
        });
        $response = $app->server->serve();
        return $response->getContent();
    }
}