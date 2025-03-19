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


}
