<?php

namespace plugin\eagleadmin\app\admin\validate\auth;

use plugin\eagleadmin\app\BaseValidate;

class DepartmentValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
        'id' => 'require',
        'head_ids' => 'checkHeadIds',
    ];

    protected $message = [
        'name.require' => '部门名称必须',
        'id.require' => 'id必传!',
    ];

    protected $scene = [
        'update' => [
            'id',
        ],
        'insert' => [
            'name',
        ],
    ];

    protected function checkHeadIds($value, $rule, $data = [], $field = '')
    {
        $headIds = $data['head_ids'] ?? [];
        if ($headIds && !is_array($headIds)) {
            return 'head_ids必须是数组！';
        }
        return true;
    }
}
