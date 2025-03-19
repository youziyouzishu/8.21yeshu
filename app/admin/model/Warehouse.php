<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 仓库名
 * @property string $lat 纬度
 * @property string $lng 经度
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse query()
 * @mixin \Eloquent
 */
class Warehouse extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_warehouse';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'name',
        'lat',
        'lng',
    ];
    


}
