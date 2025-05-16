<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\GoodsOrdersSubs;
use app\api\basic\Base;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use EasyWeChat\MiniApp\Application;
use support\Db;
use support\Request;
use Yansongda\Pay\Pay;

class NotifyController extends Base
{
    protected array $noNeedLogin = ['*'];

    function alipay(Request $request)
    {
        $request->setParams('get', ['paytype' => 'alipay']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return response('success');
    }

    function wechat(Request $request)
    {
        $request->setParams('get', ['paytype' => 'wechat']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return json(['code' => 'FAIL', 'message' => $e->getMessage()]);
        }
        return json(['code' => 'SUCCESS', 'message' => '成功']);
    }

    function balance(Request $request)
    {
        $request->setParams('get', ['paytype' => 'balance']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 接受回调
     * @throws \Throwable
     */
    private function pay(Request $request)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $paytype = $request->input('paytype');
            $config = config('payment');
            switch ($paytype) {
                case 'wechat':
                    $pay = Pay::wechat($config);
                    $res = $pay->callback($request->post(),[
                        '_config' => 'DriverMiniApp'
                    ]);
                    $res = $res->resource;
                    $res = $res['ciphertext'];
                    $trade_state = $res['trade_state'];
                    if ($trade_state !== 'SUCCESS'){
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res['out_trade_no'];
                    $attach = $res['attach'];
//                    $mchid = $res['mchid'];
//                    $transaction_id = $res['transaction_id'];
//                    $openid = $res['payer']['openid'] ?? '';
//                    $app = new Application(config('wechat'));
//                    $api = $app->getClient();
//                    $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Asia/Shanghai'));
//                    $formatted_date = $date->format('c');
//                    $api->postJson('/wxa/sec/order/upload_shipping_info', [
//                        'order_key' => ['order_number_type' => 1, 'mchid' => $mchid, 'out_trade_no' => $out_trade_no],
//                        'logistics_type' => 3,
//                        'delivery_mode' => 1,
//                        'shipping_list' => [[
//                            'item_desc' => '发货'
//                        ]],
//                        'upload_time' => $formatted_date,
//                        'payer' => ['openid' => $openid]
//                    ]);
                    break;
                case 'alipay':
                    $pay = Pay::alipay($config);
                    $res = $pay->callback($request->post());
                    $trade_status = $res->trade_status;
                    if ($trade_status !== 'TRADE_SUCCESS'){
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res->out_trade_no;
                    $attach = $res->passback_params;
                    break;
                case 'balance':
                    $out_trade_no = $request->input('out_trade_no');
                    $attach = $request->input('attach');
                    break;
                default:
                    throw new \Exception('支付类型错误');
            }

            switch ($attach) {
                case 'goods':
                    dump($out_trade_no);
                    $order = GoodsOrders::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }

                    $order->status = 1;
                    $order->pay_type = $paytype == 'wechat' ? 1 : ($paytype == 'balance' ? 2 : 3);
                    $order->pay_time = Carbon::now();
                    $order->subs()->each(function (GoodsOrdersSubs $sub) {
                        //增加销量
                        $sub->goods()->increment('sales', $sub->num);
                    });
                    $order->save();
                    break;
                default:
                    throw new \Exception('回调错误');
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getLine());
            Db::connection('plugin.admin.mysql')->rollBack();
            throw new \Exception($e->getMessage());
        }
    }

}
