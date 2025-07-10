<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $name 车辆名称
 * @property string $image 车辆图片
 * @property string $color 车辆颜色
 * @property integer $warehouse_id 所属仓库
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property-read \app\admin\model\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car query()
 * @mixin \Eloquent
 */
class Car extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_cars';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
    
    
    
}
