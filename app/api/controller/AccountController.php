<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\Factory;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{
    protected array $noNeedLogin = ['login', 'refreshToken'];

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
            if ($client_type == 'user') {
                $config = config('wechat.UserMiniApp');
            } elseif ($client_type == 'transport') {
                $config = config('wechat.TransportMiniApp');
            } elseif ($client_type == 'driver') {
                $config = config('wechat.DriverMiniApp');
            } else {
                return $this->fail('客户端类型错误');
            }
            $app = Factory::miniProgram($config);
            $ret = $app->auth->session($code);

            $openid = 1;
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
                'openid' => $openid,
                'client_type' => $client_type,
                'work_status' => $client_type == 'user' ? 1 : 2,#用户端默认为启用
                'status' => 1,
            ]);
        }

        $user->last_time = Carbon::now();
        $user->last_ip = $request->getRealIp();
        $user->save();
        if ($user->status == 0){
            return  $this->fail('账号被封禁');
        }
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'openid' => $user->openid,
            'client_type' => $client_type,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        $user->token = $token['access_token'];
        $user->save();
        return $this->success('登录成功', ['user' => $user, 'token' => $token]);
    }

    function refreshToken()
    {
        $res = JwtToken::refreshToken();
        return $this->success('成功', $res);
    }
}
