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
 * @property int $type 提现类型:1=银行卡提现,2=微信提现
 * @property string $withdraw_amount 提现金额
 * @property string $chance_amount 手续费
 * @property string $into_amount 到账金额
 * @property string $chance_rate 手续费比例
 * @property int $status 状态:0=待审核,1=已打款,2=驳回
 * @property string|null $reason 驳回原因
 * @property int|null $bankcard_id 银行卡
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw withoutTrashed()
 * @property-read mixed $status_text
 * @property-read \app\admin\model\User|null $user
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @mixin \Eloquent
 */
class UsersWithdraw extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'type',
        'withdraw_amount',
        'chance_amount',
        'into_amount',
        'chance_rate',
        'status',
        'reason',
        'bankcard_id',
    ];

    protected $appends = [
        'status_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value]??'';
    }

    function getStatusList()
    {
        return [
            0 => '待审核',
            1 => '已打款',
            2 => '驳回',
        ];
    }



}
