<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $name 名称
 * @property string $mobile 手机号
 * @property string $province 省
 * @property string $city 市
 * @property string $region 区
 * @property string $address 详细地址
 * @property string $lng 经度
 * @property string $lat 纬度
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse query()
 * @property string|null $deleted_at 删除时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\User> $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\WarehouseSku> $sku
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
        'mobile',
        'province',
        'city',
        'region',
        'address',
        'lng',
        'lat',
    ];

    function user()
    {
        return $this->hasMany(User::class, 'warehouse_id','id');
    }

    function sku()
    {
        return $this->hasMany(WarehouseSku::class, 'warehouse_id','id');
    }
    
    
    
}
