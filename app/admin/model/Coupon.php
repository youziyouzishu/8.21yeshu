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
 * @property-read mixed $type_text
 * @mixin \Eloquent
 */
class Coupon extends Base
{

    /**
     * The table associated with the model.
     *
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

    protected $appends = ['type_text'];


    function getTypeTextAttribute($value)
    {
        $value = $value ?: ($this->type ?? '');
        $list = ['1' => '无门槛', '2' => '满减'];
        return $list[$value] ?? '';
    }




}
