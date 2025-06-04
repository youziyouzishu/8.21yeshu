<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\WarehouseTakeLog;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 盘点清单 
 */
class WarehouseTakeLogController extends Crud
{
    
    /**
     * @var WarehouseTakeLog
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new WarehouseTakeLog;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('warehouse-take-log/index');
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
        return view('warehouse-take-log/insert');
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
        return view('warehouse-take-log/update');
    }

}
