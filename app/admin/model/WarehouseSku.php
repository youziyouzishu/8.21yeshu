<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSku newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSku newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSku query()
 * @property int|null $goods_id 商品
 * @property int|null $num 数量
 * @property int|null $warehouse_id 仓库
 * @property-read \app\admin\model\Warehouse|null $warehouse
 * @property-read \app\admin\model\Goods|null $goods
 * @mixin \Eloquent
 */
class WarehouseSku extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_warehouse_sku';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'warehouse_id',
        'sku_id',
        'num',
    ];

    function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }




}
