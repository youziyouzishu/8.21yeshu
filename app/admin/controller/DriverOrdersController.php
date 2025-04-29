<?php

namespace app\admin\controller;

use app\admin\model\DriverOrdersSku;
use app\api\service\Pay;
use support\Request;
use support\Response;
use app\admin\model\DriverOrders;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 司机订单 
 */
class DriverOrdersController extends Crud
{
    
    /**
     * @var DriverOrders
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DriverOrders;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('driver-orders/index');
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
        $query = $this->doSelect($where, $field, $order)->with(['user','fromWarehouse','toWarehouse']);
        return $this->doFormat($query, $format, $limit);
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
            $user_id = $request->post('user_id');
            $from_warehouse_id = $request->post('from_warehouse_id');
            $to_warehouse_id = $request->post('to_warehouse_id');
            $freight_type = $request->post('freight_type');
            $freight = $request->post('freight');
            $goods_id = $request->post('goods_id');
            $goods_quantity = $request->post('goods_quantity');

            if (empty($goods_id[0] )|| empty($goods_quantity[0])){
                return $this->fail('请添加商品信息');
            }
            $mergedArray = [];

            foreach ($goods_id as $index => $id) {
                $mergedArray[] = array(
                    'goods_id' => $id,
                    'goods_quantity' => $goods_quantity[$index]
                );
            }

            $request->setParams('post',[
                'ordersn' => Pay::generateOrderSn(),
            ]);

            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            foreach ($mergedArray as $item){
                DriverOrdersSku::create([
                    'order_id' => $id,
                    'goods_id' => $item['goods_id'],
                    'num' => $item['goods_quantity']
                ]);
            }
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('driver-orders/insert');
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
        return view('driver-orders/update');
    }

}
