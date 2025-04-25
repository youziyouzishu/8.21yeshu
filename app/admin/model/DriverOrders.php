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




}
