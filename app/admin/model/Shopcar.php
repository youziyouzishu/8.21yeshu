<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $goods_id 商品
 * @property int $num 数量
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shopcar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shopcar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shopcar query()
 * @property-read \app\admin\model\Goods|null $goods
 * @mixin \Eloquent
 */
class Shopcar extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shopcar';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'goods_id',
        'num',
    ];

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id','id');
    }

}
