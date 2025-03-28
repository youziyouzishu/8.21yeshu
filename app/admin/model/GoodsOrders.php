<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $ordersn 订单编号
 * @property string $pay_amount 支付金额
 * @property string $coupon_amount 优惠券金额
 * @property string $goods_amount 商品金额
 * @property string $freight 运费
 * @property int $status 状态:0=待支付,1=支付成功,2=取消
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders query()
 * @property int $address_id 收货地址
 * @property int $warehouse_id 仓库
 * @property float $distance 距离
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\GoodsOrdersSubs> $subs
 * @property string $mark 备注
 * @property int $delivery_type 配送类型:1=立即配送,2=预约配送
 * @property string $delivery_time 配送时间
 * @property int $invoice_id 发票
 * @property int $pay_type 支付类型:1=微信,2=余额
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @mixin \Eloquent
 */
class GoodsOrders extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'pay_time' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'user_id',
        'ordersn',
        'pay_amount',
        'coupon_amount',
        'goods_amount',
        'freight',
        'status',
        'address_id',
        'warehouse_id',
        'distance',
        'mark',
        'delivery_type',
        'delivery_time',
        'invoice_id',
        'pay_type',
        'pay_time',
    ];


    public static function generateOrderSn()
    {
        return date('Ymd') . mb_strtoupper(uniqid());
    }

    function subs()
    {
        return $this->hasMany(GoodsOrdersSubs::class, 'order_id', 'id');
    }




}
