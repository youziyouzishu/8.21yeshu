<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\MiniApp\Application;
use plugin\admin\app\common\Util;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{
    protected array $noNeedLogin = ['login'];

    /**
     * 登录
     * @param Request $request
     * @return \support\Response
     */
    function login(Request $request)
    {

        $code = $request->post('code');
        $client_type = $request->post('client_type');#user=用户端,transport=配送端,driver=司机端
        if (!in_array($client_type, ['user', 'transport', 'driver'])) {
            return $this->fail('客户端类型错误');
        }
        try {
            $app = new Application(config('wechat.UserMiniApp'));
            $ret = $app->getUtils()->codeToSession($code);
            $openid = $ret['openid'];
            $unionid = '123456';

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        $user = User::where(['openid' => $openid, 'client_type' => $client_type])->first();
        if (!$user) {
            $user = User::create([
                'nickname' => '用户' . mt_rand(100000, 999999),
                'avatar' => '/app/admin/avatar.png',
                'join_time' => Carbon::now()->toDateTimeString(),
                'join_ip' => $request->getRealIp(),
                'last_time' => Carbon::now()->toDateTimeString(),
                'last_ip' => $request->getRealIp(),
                'openid' => $openid,
                'unionid' => $unionid,
                'client_type' => $client_type,
            ]);
        }
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'openid' => $openid,
            'client_type' => $client_type,
        ]);
        return $this->success('登录成功', ['user' => $user, 'token' => $token]);
    }
}
