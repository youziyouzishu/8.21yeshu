<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $truename 真实姓名
 * @property string $cardnum 银行卡号
 * @property string $bank 银行
 * @property string $open_bank 开户行
 * @property string $mobile 手机号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard query()
 * @property-read \app\admin\model\User|null $user
 * @property string|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBankcard withoutTrashed()
 * @mixin \Eloquent
 */
class UsersBankcard extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_bankcard';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'truename',
        'cardnum',
        'bank',
        'open_bank',
        'mobile',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



}
