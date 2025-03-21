<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;
use Tinywan\Jwt\JwtToken;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 券名称
 * @property int $type 券类型:1=无门槛,2=满减
 * @property string $amount 优惠金额
 * @property string $with_amount 满足金额
 * @property string|null $mark 备注
 * @property int $status 状态:0=下架,1=上架
 * @property int $expired_day 领取失效天数
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon query()
 * @mixin \Eloquent
 */
class Coupon extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_coupon';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';



    public static function getCouponAmount($amount,$coupon_id)
    {
        $coupon_amount = 0;
        if (!empty($coupon_id)) {
            $usercoupon = UsersCoupon::where(['id' => $coupon_id])->first();
            if (empty($usercoupon)) {
                throw new \Exception('优惠券不存在', 1);
            }
            if ($usercoupon->user_id != JwtToken::getCurrentId()) {
                throw new \Exception('优惠券不正确', 1);
            }
            if ($usercoupon->status != 1) {
                throw new \Exception('优惠券不存在', 1);

            }
            if ($usercoupon->type == 2 && $usercoupon->with_amount > $amount) {
                throw new \Exception('不满足满减条件', 1);
            }
            $coupon_amount = $usercoupon->amount;
        }
        return $coupon_amount;
    }




}
