<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\UsersAddress;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Request;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    function index(Request $request)
    {

        dump(md5(123456));

    }

}
