<?php

namespace app\admin\controller;

use app\admin\model\WarehouseSku;
use support\Request;
use support\Response;
use app\admin\model\WarehouseTake;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 盘点管理 
 */
class WarehouseTakeController extends Crud
{
    
    /**
     * @var WarehouseTake
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new WarehouseTake;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['warehouse','operate']);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('warehouse-take/index');
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
        return view('warehouse-take/insert');
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
            if (empty($operate_id)) {
                $request->setParams('post',[
                    'operate_id' => admin_id(),
                ]);
            }
            return parent::update($request);
        }
        return view('warehouse-take/update');
    }

}
