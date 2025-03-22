<?php

namespace app\api\controller;

use app\admin\model\Shopcar;
use app\admin\model\User;
use app\api\basic\Base;
use support\Request;
use Tinywan\Jwt\JwtToken;
use Webman\Exception\BusinessException;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    function index(Request $request)
    {
        $user = User::find(1);
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'openid' => $user->openid,
            'client_type' => $user->client_type,
        ]);
        return $this->success('成功', ['user' => $user, 'token' => $token]);
    }



}
