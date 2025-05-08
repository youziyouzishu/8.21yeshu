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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSkuLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSkuLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseSkuLog query()
 * @property int $warehouse_id 仓库
 * @property int $goods_id 商品
 * @property int $num 数量
 * @property string|null $mark 备注
 * @property int $type 类型:1=出库,2=入库
 * @property-read \app\admin\model\Goods|null $goods
 * @property-read \app\admin\model\Warehouse|null $warehouse
 * @mixin \Eloquent
 */
class WarehouseSkuLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_warehouse_sku_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'warehouse_id',
        'goods_id',
        'num',
        'mark',
        'type',
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
