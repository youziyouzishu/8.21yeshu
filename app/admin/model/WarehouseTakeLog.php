<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $take_id 盘点
 * @property integer $goods_id 商品
 * @property integer $num 数量
 * @property integer $difference 差值
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property-read \app\admin\model\Goods|null $goods
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTakeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTakeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTakeLog query()
 * @mixin \Eloquent
 */
class WarehouseTakeLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_warehouse_take_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'take_id',
        'goods_id',
        'num',
        'difference',
    ];

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    
    
    
}
