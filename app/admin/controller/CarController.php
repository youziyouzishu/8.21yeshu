<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Car;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 车辆管理 
 */
class CarController extends Crud
{
    
    /**
     * @var Car
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Car;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('car/index');
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
        return view('car/insert');
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
        return view('car/update');
    }

}
