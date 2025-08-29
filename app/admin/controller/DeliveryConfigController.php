<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\DeliveryConfig;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 配送价格表 
 */
class DeliveryConfigController extends Crud
{
    
    /**
     * @var DeliveryConfig
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DeliveryConfig;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('delivery-config/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('delivery-config/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('delivery-config/update');
    }

}
