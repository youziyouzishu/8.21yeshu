<?php

namespace plugin\wechat\app\admin\controller;

use plugin\wechat\app\service\WechatService;
use support\Response;

class MenuController
{
    public function index(): Response
    {
        return raw_view('menu/index');
    }

    public function load(): Response
    {
        $res = WechatService::instance()->menu->current();
        if (isset($res['errmsg'])) {
            return json(['code' => 1, 'msg' => '微信接口返回错误:' . $res['errmsg']]);
        } else if ($res['is_menu_open'] === 0) {
            return json(['code' => 1, 'msg' => '菜单未开启或加载失败,如果是新创建的公众号,请先直接添加菜单即可']);
        } else {
            $menu = $res['selfmenu_info']['button'];
            foreach ($menu as $k => $v) {
                if (isset($v['sub_button'])) {
                    $menu[$k]['sub_button'] = $v['sub_button']['list'];
                }
            }
            return json(['code' => 0, 'data' => $menu]);
        }
    }

    public function create(): Response
    {

        $menu = request()->post('menu');
        if ($menu === null) {
            return json(['code' => 1, 'msg' => '菜单数据不能为空']);
        }
        try {
            $res = WechatService::instance()->menu->create($menu);
            if ($res['errcode'] === 0) {
                return json(['code' => 0, 'msg' => '发布成功']);
            } else {
                return json(['code' => 1, 'msg' => '微信接口返回错误:' . $res['errmsg']]);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        }

    }
}