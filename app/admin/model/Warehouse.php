<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
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
    
    
    
}
