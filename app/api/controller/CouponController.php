<?php

namespace app\api\controller;

use app\admin\model\Coupon;
use app\admin\model\UsersCoupon;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;
use Webman\RedisQueue\Client;

class CouponController extends Base
{
    protected array $noNeedLogin = ['select'];

    function select(Request $request)
    {
        $coupons = Coupon::where(['status' => 1])->get();
        $coupons = $coupons->map(function ($coupon) {
            $coupon->expired_at = Carbon::now()->addDays($coupon->expired_day)->toDateString();
            return $coupon;
        });
        // 根据 type 字段分组
        $groupedCoupons = $coupons->groupBy('type');
        // 将分组结果转换为数组
        $result = [
            ['name' => '无门槛券', 'list' => $groupedCoupons[1] ?? []],
            ['name' => '满减券', 'list' => $groupedCoupons[2] ?? []]
        ];
        return $this->success('成功', $result);
    }


    function receive(Request $request)
    {
        $id = $request->post('id');
        $coupon = Coupon::find($id);
        if (UsersCoupon::where(['user_id' => $request->user_id, 'coupon_id' => $id])->exists()) {
            return $this->fail('已领取');
        } else {
            $expired_at = Carbon::now()->addDays($coupon->expired_day);
            $user_coupon = UsersCoupon::create([
                'user_id' => $request->user_id,
                'coupon_id' => $id,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'amount' => $coupon->amount,
                'with_amount' => $coupon->with_amount,
                'expired_at' => $expired_at->toDateTimeString(),
            ]);
            Client::send('job', ['event' => 'user_coupon_expire', 'id' => $user_coupon->id], $expired_at->timestamp - time());
        }
        return $this->success('成功');
    }



}
