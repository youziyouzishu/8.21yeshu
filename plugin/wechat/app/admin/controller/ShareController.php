<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\model\Option;
use support\Response;

class ShareController
{
    public function index(): Response
    {
        return raw_view('share/index');
    }

    public function get(): Response
    {
        $share_config = Option::where('name', 'wechat_share')->value('value');
        if (empty($share_config)) {
            $share_config = '{}';
            $option = new Option();
            $option->name = 'wechat_share';
            $option->value = $share_config;
            $option->save();
        }
        return json(['code' => 0, 'data' => json_decode($share_config, true)]);
    }

    public function save(): Response
    {
        $share_config = request()->post();
        Option::where('name', 'wechat_share')->update(['value' => json_encode($share_config)]);
        return json(['code' => 0, 'msg' => '保存成功']);
    }
}