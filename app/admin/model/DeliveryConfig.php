<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property int $start 开始距离/km
 * @property int $end 结束距离/km
 * @property string $price 价格
 * @property int $weigh 权重
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryConfig query()
 * @mixin \Eloquent
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
