<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $goods_id 商品
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCollect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCollect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersCollect query()
 * @property-read \app\admin\model\Goods|null $goods
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UsersCollect extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_collect';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'goods_id',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

}
