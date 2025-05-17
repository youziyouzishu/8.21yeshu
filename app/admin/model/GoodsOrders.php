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
 * @property int $address_id 收货地址
 * @property int $warehouse_id 仓库
 * @property int $delivery_type 配送类型:1=立即配送,2=预约配送
 * @property string $delivery_time 配送时间
 * @property string $ordersn 订单编号
 * @property string $pay_amount 支付金额
 * @property string $coupon_amount 优惠券金额
 * @property string $goods_amount 商品金额
 * @property string $freight 运费
 * @property float $distance 距离
 * @property string $mark 备注
 * @property int $invoice_id 发票
 * @property int $pay_type 支付类型:0=无,1=微信,2=余额
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\GoodsOrdersSubs> $subs
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders query()
 * @property int $status 状态:0=待支付,1=待商家接单,2=取消,3=待骑手接单,4=待骑手取货,5=待骑手取货(已废弃),6=配送中,7=配送完成,8=退款成功,9=订单完成
 * @property-read mixed $status_text
 * @property \Illuminate\Support\Carbon|null $cancel_time 取消时间
 * @property \Illuminate\Support\Carbon|null $confirm_time 确认时间
 * @property \Illuminate\Support\Carbon|null $arrival_time 送达时间
 * @property-read \app\admin\model\UsersAddress|null $address
 * @property-read \app\admin\model\User|null $user
 * @property-read mixed $delivery_type_text
 * @property-read \app\admin\model\UsersInvoice|null $invoice
 * @property-read \app\admin\model\Warehouse|null $warehouse
 * @property int|null $transport_id 配送员
 * @property int|null $distribute_type 派单类型:1=手动派单,2=自动派单
 * @property-read \app\admin\model\User|null $transport
 * @property-read mixed $distribute_type_text
 * @property \Illuminate\Support\Carbon|null $distribute_time 派单时间
 * @property \Illuminate\Support\Carbon|null $accept_time 接单时间
 * @property string|null $cancel_reason 取消原因
 * @property string|null $cancel_explain 取消说明
 * @property string|null $cancel_images 取消凭证
 * @property \Illuminate\Support\Carbon|null $reach_time 到店时间
 * @property \Illuminate\Support\Carbon|null $take_time 取货时间
 * @property int $settle_status 结算状态:0=无,1=未结算,2=已结算
 * @property-read mixed $pay_type_text
 * @property-read mixed $settle_status_text
 * @property string|null $accept_lng 接单经度
 * @property string|null $accept_lat 接单纬度
 * @property \Illuminate\Support\Carbon|null $timeout_time 配送超时时间
 * @property string|null $total_distance 总距离
 * @property int $timeout_status 超时状态:0=无,1=未超时,2=超时
 * @property-read mixed $timeout_status_text
 * @property string|null $total_time 总配送时长(分钟)
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
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'cancel_time' => 'datetime:Y-m-d H:i:s',
        'confirm_time' => 'datetime:Y-m-d H:i:s',
        'arrival_time' => 'datetime:Y-m-d H:i:s',
        'distribute_time' => 'datetime:Y-m-d H:i:s',
        'accept_time' => 'datetime:Y-m-d H:i:s',
        'reach_time' => 'datetime:Y-m-d H:i:s',
        'take_time' => 'datetime:Y-m-d H:i:s',
        'timeout_time' => 'datetime:Y-m-d H:i:s',
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
        'settle_status'
    ];

    protected $appends = [
        'status_text',
        'delivery_type_text',
        'distribute_type_text',
        'pay_type_text',
        'settle_status_text',
        'timeout_status_text',
    ];

    function subs()
    {
        return $this->hasMany(GoodsOrdersSubs::class, 'order_id', 'id');
    }

    function getDistributeTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->delivery_type;
        $list = $this->getDistributeTypeList();
        return $list[$value] ?? '';
    }

    function getDistributeTypeList()
    {
        return [
            1 => '手动派送',
            2 => '自动派送',
        ];
    }

    function getDeliveryTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->delivery_type;
        $list = $this->getDeliveryTypeList();
        return $list[$value] ?? '';
    }

    function getDeliveryTypeList()
    {
        return [
            1 => '立即配送',
            2 => '预约配送',
        ];
    }


    function getSettleStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->settle_status;
        $list = $this->getSettleStatusList();
        return $list[$value] ?? '';
    }

    function getSettleStatusList()
    {
        return [
            0 => '无',
            1 => '未结算',
            2 => '已结算',
        ];
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    function getStatusList()
    {
        return [
            0 => '待支付',
            1 => '待商家接单',
            2 => '取消',
            3 => '待骑手接单',
            4 => '待骑手取货',
            5 => '待骑手取货',
            6 => '配送中',
            7 => '待评价',
            8 => '退款成功',
            9 => '订单完成',
        ];
    }

    function getPayTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->pay_type;
        $list = $this->getPayTypeList();
        return $list[$value] ?? '';
    }

    function getPayTypeList()
    {
        return [
            0 => '无',
            1 => '微信',
            2 => '余额',
        ];
    }


    function getTimeoutStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->timeout_status;
        $list = $this->getTimeoutStatusList();
        return $list[$value] ?? '';
    }

    function getTimeoutStatusList()
    {
        return [
            0 => '无',
            1 => '未超时',
            2 => '超时',
        ];
    }



    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function address()
    {
        return $this->belongsTo(UsersAddress::class, 'address_id', 'id');
    }

    function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    function invoice()
    {
        return $this->belongsTo(UsersInvoice::class, 'invoice_id', 'id');
    }

    function transport()
    {
        return $this->belongsTo(User::class, 'transport_id', 'id');
    }


}
