<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $goods_id 商品
 * @property int $num 数量
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrdersSku newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrdersSku newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DriverOrdersSku query()
 * @property-read \app\admin\model\DriverOrders|null $orders
 * @mixin \Eloquent
 */
class DriverOrdersSku extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_driver_orders_sku';

    protected $fillable = ['order_id', 'goods_id', 'num'];


    function orders()
    {
        return $this->belongsTo(DriverOrders::class, 'order_id', 'id');
    }


}
