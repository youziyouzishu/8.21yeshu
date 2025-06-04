<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $operate_id 操作者
 * @property integer $warehouse_id 仓库
 * @property integer $status 状态:0=待审核,1=通过,2=驳回
 * @property string $mark 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
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
    
    
    
}
