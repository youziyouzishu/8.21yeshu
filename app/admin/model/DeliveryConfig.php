<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $start 开始距离/km
 * @property integer $end 结束距离/km
 * @property string $price 价格
 * @property integer $weigh 权重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class DeliveryConfig extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_delivery_config';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
