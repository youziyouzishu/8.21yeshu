<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $image 封面
 * @property string $title 标题
 * @property string $content 内容
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property integer $coupon_id 优惠券
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Activity extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_activity';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function coupon()
    {
        return $this->belongsTo(Coupon::class,'coupon_id','id');
    }
    
    
    
}
