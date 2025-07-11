<?php

namespace app\admin\controller;

use app\admin\model\User;
use app\admin\model\UsersCoupon;
use Carbon\Carbon;
use support\Request;
use support\Response;
use app\admin\model\Coupon;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use Webman\RedisQueue\Client;

/**
 * 优惠券管理 
 */
class CouponController extends Crud
{
    
    /**
     * @var Coupon
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Coupon;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('coupon/index');
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
        return view('coupon/insert');
    }

    /**
     * 发放优惠券
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function give(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $user_ids = $request->post('user_id');
            $coupon_ids = $request->post('coupon_id');
            $coupon_ids = explode(',', $coupon_ids);
            $user_ids = explode(',', $user_ids);
            foreach ($user_ids as $user_id) {
                foreach ($coupon_ids as $coupon_id) {
                    $coupon = Coupon::find($coupon_id);
                    $expired_at = Carbon::now()->addDays($coupon->expired_day);
                    $user_coupon = UsersCoupon::create([
                        'user_id' => $user_id,
                        'coupon_id' => $coupon_id,
                        'name' => $coupon->name,
                        'type' => $coupon->type,
                        'amount' => $coupon->amount,
                        'with_amount' => $coupon->with_amount,
                        'expired_at' => $expired_at->toDateTimeString(),
                    ]);
                    Client::send('job', ['event' => 'user_coupon_expire', 'id' => $user_coupon->id], $expired_at->timestamp - time());
                }
            }
            return $this->success();
        }
        return view('coupon/give');
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
        return view('coupon/update');
    }

}
