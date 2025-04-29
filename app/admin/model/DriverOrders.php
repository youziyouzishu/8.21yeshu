<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $from_warehouse_id 发货仓库
 * @property int $to_warehouse_id 目的地
 * @property int $freight_type 类型:1=线上结算,2=线下结算
 * @property string|null $freight 运费
 * @property int $status 状态:0=待分配,1=待接受,2=待取货,3=取消,4=配送中,5=完成
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders query()
 * @property-read mixed $freight_type_text
 * @property-read mixed $status_text
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\DriverOrdersSku> $sku
 * @property-read \app\admin\model\Warehouse|null $fromWarehouse
 * @property-read \app\admin\model\Warehouse|null $toWarehouse
 * @property-read \app\admin\model\User|null $user
 * @property string $ordersn 订单编号
 * @property string|null $accept_lng 接单经度
 * @property string|null $accept_lat 接单纬度
 * @property string|null $total_distance 总距离
 * @property int $timeout_status 超时状态:0=无,1=未超时,2=超时
 * @property string|null $total_time 总配送时长(分钟)
 * @property int $settle_status 结算状态:0=无,1=未结算,2=已结算
 * @property \Illuminate\Support\Carbon|null $accept_time 接单时间
 * @property \Illuminate\Support\Carbon|null $timeout_time 配送超时时间
 * @property string|null $cancel_reason 取消原因
 * @property string|null $cancel_explain 取消说明
 * @property string|null $cancel_images 取消凭证
 * @property \Illuminate\Support\Carbon|null $reach_time 到店时间
 * @property \Illuminate\Support\Carbon|null $take_time 取货时间
 * @property \Illuminate\Support\Carbon|null $arrival_time 送达时间
 * @mixin \Eloquent
 */
class DriverOrders extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_driver_orders';


    protected $fillable = [
        'user_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'freight_type',
        'freight',
        'status',
    ];


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $appends = [
        'status_text',
        'freight_type_text',
    ];

    protected $casts = [
        'accept_time' => 'datetime:Y-m-d H:i:s',
        'timeout_time' => 'datetime:Y-m-d H:i:s',
        'reach_time' => 'datetime:Y-m-d H:i:s',
        'take_time' => 'datetime:Y-m-d H:i:s',
        'arrival_time' => 'datetime:Y-m-d H:i:s',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id', 'id');
    }

    function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id', 'id');
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
            0 => '待分配',
            1 => '待接受',
            2 => '待取货',
            3 => '取消',
            4 => '配送中',
            5 => '完成',
        ];
    }

    public function getFreightTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->freight_type;
        $list = $this->getFreightTypeList();
        return $list[$value] ?? '';
    }

    public function getFreightTypeList()
    {
        return [
            1 => '线上结算',
            2 => '线下结算',
        ];
    }

    function sku()
    {
        return $this->hasMany(DriverOrdersSku::class, 'order_id', 'id');
    }




}
