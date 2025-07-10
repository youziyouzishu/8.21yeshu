<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\GoodsOrders;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use Webman\RedisQueue\Client;

/**
 * 订单列表 
 */
class GoodsOrdersController extends Crud
{
    
    /**
     * @var GoodsOrders
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new GoodsOrders;
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
        $query = $this->doSelect($where, $field, $order)->with(['user','address','warehouse','invoice','transport','depositAddress']);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('goods-orders/index');
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
        return view('goods-orders/insert');
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
            $id = $request->post('id');
            $status = $request->post('status');
            $transport_id = $request->post('transport_id');
            $distribute_type = $request->post('distribute_type');#派单类型:1=手动派单,2=自动派单
            $order = GoodsOrders::find($id);
            if ($order->status == 1 && $status == 3){
                //发单 指定配送员
                if ($distribute_type == 1){
                    if (empty($transport_id)){
                        return $this->fail('请选择配送员');
                    }
                }
                if ($distribute_type == 2){
                    //自动派单
                    Client::send('job', ['event' => 'distribute_order', 'id' => $order->id]);
                }
            }
            return parent::update($request);
        }
        return view('goods-orders/update');
    }

}
