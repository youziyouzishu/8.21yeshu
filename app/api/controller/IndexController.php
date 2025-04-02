<?php

namespace app\api\controller;

use app\admin\model\PrivacyService;
use app\api\basic\Base;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Timer;

class IndexController extends Base
{

    protected array $noNeedLogin = ['index','test'];

    function index()
    {

    }


}
