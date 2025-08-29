<?php

namespace app\admin\controller;

use app\admin\model\WarehouseSku;
use app\admin\model\WarehouseTake;
use support\Request;
use support\Response;
use app\admin\model\Warehouse;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 仓库管理 
 */
class WarehouseController extends Crud
{
    
    /**
     * @var Warehouse
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Warehouse;
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
        $query = $this->doSelect($where, $field, $order)->withCount(['log'=>function ($query) {
            $query->where('type',1);
        }]);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('warehouse/index');
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
        return view('warehouse/insert');
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
        return view('warehouse/update');
    }

    /**
     * 盘点
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function take(Request $request): Response
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
            return $this->success('盘点成功');
        }
        $sku = WarehouseSku::with(['goods'])->where('warehouse_id',$warehouse_id)->get();
        return view('warehouse/take',['sku'=>$sku,'warehouse_id'=>$warehouse_id]);
    }

}
