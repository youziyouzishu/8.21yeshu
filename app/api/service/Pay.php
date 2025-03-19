<?php

namespace app\api\service;

use support\exception\BusinessException;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

class Pay
{
    /**
     * 支付
     * @param $pay_type *支付类型:1=微信,2=支付宝,3=数字人民币
     * @param  $pay_amount
     * @param  $order_no
     * @param $mark
     * @param $attach
     * @return string|Rocket|Collection
     * @throws \Exception
     */
    public static function pay($pay_type, $pay_amount, $order_no, $mark, $attach)
    {
        $config = config('payment');
        if ($pay_type == 1) {
            $result = \Yansongda\Pay\Pay::wechat($config)->app([
                'out_trade_no' => $order_no,
                'description' => $mark,
                'amount' => [
                    'total' => function_exists('bcmul') ? (int)bcmul($pay_amount, 100, 2) : $pay_amount * 100,
                    'currency' => 'CNY',
                ],
                'attach' => $attach
            ]);
        } elseif ($pay_type == 2) {
            $result = \Yansongda\Pay\Pay::alipay($config)->app([
                'out_trade_no' => $order_no,
                'total_amount' => $pay_amount,
                'subject' => $mark,
                'passback_params' => urlencode($attach)
            ])->getBody()->getContents();
        } elseif ($pay_type == 3) {
            throw new \Exception('暂不支持数字人民币');
        }else{
            throw new \Exception('支付类型错误');
        }
        return $result;
    }

    #退款
    public static function refund($pay_type, $pay_amount, $order_no, $refund_order_no, $reason)
    {
        $config = config('payment');
        return match ($pay_type) {
            1 => \Yansongda\Pay\Pay::wechat($config)->refund([
                'out_trade_no' => $order_no,
                'out_refund_no' => $refund_order_no,
                'amount' => [
                    'refund' => (int)bcmul($pay_amount, 100, 2),
                    'total' => (int)bcmul($pay_amount, 100, 2),
                    'currency' => 'CNY',
                ],
                'reason' => $reason
            ]),
            default => throw new \Exception('支付类型错误'),
        };
    }
}