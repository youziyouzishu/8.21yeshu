<?php

namespace app\admin\model;

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $operate_id 操作者
 * @property integer $warehouse_id 仓库
 * @property integer $status 状态:0=待审核,1=通过,2=驳回
 * @property string $mark 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\WarehouseTakeLog> $log
 * @property-read Admin|null $operate
 * @property-read \app\admin\model\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTake newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTake newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseTake query()
 * @mixin \Eloquent
 */
class WarehouseTake extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_warehouse_take';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'operate_id',
        'warehouse_id',
        'status',
        'mark',
        'created_at',
        'updated_at',
    ];

    function log()
    {
        return $this->hasMany(WarehouseTakeLog::class, 'take_id', 'id');
    }

    function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    function operate()
    {
        return $this->belongsTo(Admin::class, 'operate_id', 'id');
    }
    
    
    
}
