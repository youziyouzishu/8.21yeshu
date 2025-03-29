<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;
use support\Db;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $ordersn 订单编号
 * @property string $amount 充值金额
 * @property int $pay_type 支付类型:0=无,1=微信
 * @property int $status 状态:0=未付款,1=已付款
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersRecharge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersRecharge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersRecharge query()
 * @mixin \Eloquent
 */
class UsersRecharge extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_recharge';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = ['user_id','ordersn','amount','pay_type','status'];


    


}
