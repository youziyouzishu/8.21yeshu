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
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrders query()
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

    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';




}
