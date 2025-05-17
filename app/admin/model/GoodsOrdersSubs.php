<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $goods_id 商品
 * @property int $num 数量
 * @property string $amount 单价
 * @property string $total_amount 商品总价
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersSubs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersSubs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersSubs query()
 * @property-read \app\admin\model\Goods|null $goods
 * @property-read \app\admin\model\GoodsOrders|null $orders
 * @property-read \app\admin\model\GoodsOrdersAssess|null $assess
 * @mixin \Eloquent
 */
class GoodsOrdersSubs extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods_orders_subs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'goods_id',
        'num',
        'amount',
        'total_amount',
    ];

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    function orders()
    {
        return $this->belongsTo(GoodsOrders::class, 'order_id', 'id');
    }

    function assess()
    {
        return $this->hasOne(GoodsOrdersAssess::class, 'sub_id', 'id');
    }




}
