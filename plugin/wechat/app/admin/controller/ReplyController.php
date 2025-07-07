<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\model\Option;
use support\Response;

class ReplyController
{
    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return view('reply/index');
    }

    /**
     * 获取配置
     * @return Response
     */
    public function get(): Response
    {
        $reply = Option::where('name', 'wechat_reply')->value('value');
        if (empty($reply)) {
            $reply = json_encode(['mode' => 'simple', 'hook' => '', 'follow' => '感谢关注我们', 'default' => '请点击菜单', 'rules' => []], JSON_UNESCAPED_UNICODE);
            $option = new Option();
            $option->name = 'wechat_reply';
            $option->value = $reply;
            $option->save();
        }
        return json(['code' => 0, 'data' => json_decode($reply, true)]);
    }

    public function save(): Response
    {
        $data = request()->post();
        $reply['mode'] = $data['mode'];
        $reply['hook'] = $data['hook'];
        $reply['follow'] = $data['follow'];
        $reply['default'] = $data['default'];
        for ($i = 0; $i < count($data['keyword']); $i++) {
            $reply['rules'][$data['keyword'][$i]] = $data['reply'][$i];
        }
        Option::where('name', 'wechat_reply')->update(['value' => json_encode($reply, JSON_UNESCAPED_UNICODE)]);
        return json(['code' => 0, 'msg' => '保存成功']);
    }
}