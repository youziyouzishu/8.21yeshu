<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $type 类型:1=水机直购,2=水机租赁,3=桶装水
 * @property string $name 商品名称
 * @property string $image 图片
 * @property string $images 轮播图
 * @property string $price 直购价
 * @property string $rent 租赁价/月
 * @property string $deposit 押金
 * @property int $sales 销量
 * @property string|null $content 内容
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UsersCollect> $collect
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\Shopcar> $shopcar
 * @property-read mixed $type_text
 * @property int $category_id 分类
 * @mixin \Eloquent
 */
class Goods extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $appends = ['type_text'];


    function collect()
    {
        return $this->hasMany(UsersCollect::class, 'goods_id', 'id');
    }

    function shopcar()
    {
        return $this->hasMany(Shopcar::class, 'goods_id', 'id');
    }

    function getTypeTextAttribute($value)
    {
        $value = $value ?: ($this->type ?? '');
        $list = ['1' => '椰树水机直购', '2' => '椰树水机租赁', '3' => '椰树长寿泉'];
        return $list[$value] ?? '';
    }


}
