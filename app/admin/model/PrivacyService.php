<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $type 类型:1=水机维修,2=水机清洗
 * @property int $address_id 地址
 * @property string $visit_time 上门时间
 * @property string $image 图片
 * @property string $mark 备注
 * @property int $status 状态:1=待受理,2=已受理,3=已取消,4=已完成
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyService query()
 * @property-read mixed $status_text
 * @mixin \Eloquent
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
    ];

    protected $appends = ['status_text'];

    function getStatusTextAttribute($value)
    {
        $value = $value ?? $this->status;
        $list = [
            1 => '待受理',
            2 => '已受理',
            3 => '已取消',
            4 => '已完成',
        ];
        return $list[$value] ?? '';
    }


}
