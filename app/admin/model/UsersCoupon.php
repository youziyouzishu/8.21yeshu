<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int|null $user_id 用户
 * @property string $name 券名称
 * @property int $type 券类型:1=无门槛,2=满减
 * @property string $amount 优惠金额
 * @property string $with_amount 满足金额
 * @property string|null $expired_at 过期时间
 * @property int $status 状态:1=未使用,2=已使用,3=已过期
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCoupon query()
 * @property int $coupon_id 优惠券
 * @mixin \Eloquent
 */
class UsersCoupon extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_coupon';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = ['coupon_id','user_id','name','type','amount','with_amount','expired_at','status'];

    protected $appends = ['type_text'];


    function getTypeTextAttribute($value)
    {
        $value = $value ?: ($this->type ?? '');
        $list = ['1' => '无门槛', '2' => '满减'];
        return $list[$value] ?? '';
    }




}
