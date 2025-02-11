<?php

namespace plugin\eagleadmin\app\model;


/**
 * sys_dict 字典表
 * @property integer $id (主键)
 * @property integer $category_id 字典分类ID
 * @property string $dict_code 字典编码
 * @property string $dict_name 字典名称
 * @property string $dict_value 字典值
 * @property mixed $create_time 创建时间
 * @property mixed $update_time 更新时间
 * @property integer $sort 排序
 * @property integer $status 状态1激活 0禁用
 */
class EgDict extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_dict';

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
    
    
}
