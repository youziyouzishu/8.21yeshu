<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户
 * @property integer $type 类型:1=水机维修,2=水机清洗
 * @property integer $address_id 地址
 * @property string $visit_time 上门时间
 * @property string $image 图片
 * @property string $mark 备注
 * @property integer $status 状态:1=待受理,2=已受理,3=已取消,4=已完成
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PrivacyService extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_privacy_service';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'type',
        'address_id',
        'visit_time',
        'image',
        'mark',
        'status',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo(UsersAddress::class, 'address_id', 'id');
    }
    
    
    
}
