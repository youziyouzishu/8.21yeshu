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

    function index(Request $request)
    {
        $connection = $request->connection;


        $id = Timer::add(0.5, function () use ($connection, &$id) {

            // 连接关闭时，清除定时器
            if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                Timer::del($id);
            }
            $connection->send(new ServerSentEvents(['data' => 'hello']));
        });

        return response('', 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }



}
