<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $image 封面
 * @property string $content 内容
 * @property integer $weigh 权重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner query()
 * @mixin \Eloquent
 */
class Banner extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_banner';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
