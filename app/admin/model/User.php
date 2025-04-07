<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $sex 性别
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property int $level 等级
 * @property string|null $birthday 生日
 * @property string $money 余额(元)
 * @property int $score 积分
 * @property string|null $last_ip 登录ip
 * @property string|null $join_ip 注册ip
 * @property string|null $token token
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $role 角色
 * @property int $status 禁用
 * @property string $openid 微信标识
 * @property string $client_type 客户端类型:user=用户端,transport=配送端,driver=司机端
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @property string|null $mobile 手机
 * @property string $unionid 开放平台标识
 * @property \Illuminate\Support\Carbon|null $last_time 登录时间
 * @property \Illuminate\Support\Carbon|null $join_time 注册时间
 * @property int $work_status 工作状态:0=否,1=是
 * @property int|null $warehouse_id 所属仓库
 * @property string|null $lat 纬度
 * @property string|null $lng 经度
 * @property string|null $truename 姓名
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UsersBankcard> $bankcard
 * @mixin \Eloquent
 */
class User extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'last_time' => 'datetime:Y-m-d H:i:s',
        'join_time' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'username',
        'nickname',
        'password',
        'sex',
        'avatar',
        'email',
        'mobile',
        'level',
        'birthday',
        'money',
        'score',
        'last_time',
        'last_ip',
        'join_time',
        'join_ip',
        'token',
        'created_at',
        'updated_at',
        'role',
        'status',
        'openid',
        'client_type',
        'unionid',
    ];


    /**
     * 变更用户余额
     * @param float $money 金额
     * @param int $user_id 用户ID
     * @param string $memo 备注
     * @throws \Throwable
     */
    public static function money($money, $user_id, $memo)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->money = $after;
                $user->save();
                //写入日志
                UsersMoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
        }
    }

    function bankcard()
    {
        return $this->hasMany(UsersBankcard::class, 'user_id', 'id');
    }




}
