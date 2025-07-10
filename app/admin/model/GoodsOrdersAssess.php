<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $sub_id 子订单
 * @property int $order_id 主订单
 * @property int $goods_id 商品
 * @property int $goods_score 商品评分(1-5)
 * @property string|null $goods_content 商品评价内容
 * @property string|null $image 图片
 * @property int $transport_id 骑手
 * @property int $transport_score 骑手评分(1-5)
 * @property string|null $transport_content 骑手评价内容
 * @property int $satisfied 满意:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersAssess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersAssess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersAssess query()
 * @property int $user_id 用户
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class GoodsOrdersAssess extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods_orders_assess';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = ['sub_id', 'order_id', 'goods_id', 'goods_score', 'goods_content', 'image', 'transport_id', 'transport_score', 'transport_content', 'satisfied', 'created_at', 'updated_at','user_id'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
