<?php

namespace plugin\eagleadmin\app\model;


/**
 * sys_dict_category 字典分类
 * @property integer $id (主键)
 * @property string $name 分类名称
 * @property string $code 编码
 * @property integer $status 状态
 * @property string $remark 字典值
 * @property mixed $create_time 创建时间
 * @property mixed $update_time 更新时间
 */
class EgDictCategory extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_dict_category';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function dict()
    {
        return $this->hasMany(EgDict::class, 'category_id', 'id');
    }
}
