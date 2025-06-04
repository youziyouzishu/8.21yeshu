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
        $warehouse_id = $request->input('warehouse_id');
        if ($request->method() === 'POST') {
            $goods_list = $request->input('goods_list');
            foreach ($goods_list as &$item){
                $num_real = WarehouseSku::where('warehouse_id',$warehouse_id)->where('goods_id',$item['goods_id'])->value('num');
                $item['difference'] = $item['num'] - $num_real;
            }
            $ret = WarehouseTake::create([
                'warehouse_id' => $warehouse_id,
            ]);
            $ret->log()->createMany($goods_list);
        }
        $sku = WarehouseSku::with(['goods'])->where('warehouse_id',$warehouse_id)->get();
        return view('warehouse-take/insert',['sku'=>$sku,'warehouse_id'=>$warehouse_id]);
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
        return view('warehouse-take/update');
    }

}
