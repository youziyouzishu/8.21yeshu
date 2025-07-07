<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\model\Option;
use support\Response;

class ConfigController
{
    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('config/index');
    }

    /**
     * 获取配置
     * @return Response
     */
    public function get(): Response
    {
        $config = Option::where('name', 'wechat_config')->value('value');
        if (empty($config)) {
            $config = json_encode(['token' => get_rand_string()]);
            $option = new Option();
            $option->name = 'wechat_config';
            $option->value = $config;
            $option->save();
        }
        return json(['code' => 0, 'data' => json_decode($config, true)]);
    }

    public function save(): Response
    {
        $config = request()->post();
        Option::where('name', 'wechat_config')->update(['value' => json_encode($config)]);
        return json(['code' => 0, 'msg' => '保存成功']);
    }
}