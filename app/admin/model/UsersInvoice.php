<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $type 类型:1=个人,2=企业
 * @property string $name 发票抬头/公司名称
 * @property string $taxpayer 纳税人识别号
 * @property string $address 企业地址
 * @property string $mobile 电话号码
 * @property string $bank 开户行
 * @property string $bank_account 银行卡号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice query()
 * @property int $default 默认:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersInvoice withoutTrashed()
 * @mixin \Eloquent
 */
class UsersInvoice extends Base
{

    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_invoice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'taxpayer',
        'address',
        'mobile',
        'bank',
        'bank_account',
        'default',
    ];




}
