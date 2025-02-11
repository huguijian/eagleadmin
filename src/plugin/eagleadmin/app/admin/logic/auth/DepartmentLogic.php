<?php

namespace plugin\eagleadmin\app\admin\logic\auth;

use plugin\eagleadmin\app\model\EmsDepartment;
use plugin\eagleadmin\app\model\EmsUser;

class DepartmentLogic
{
    /**
     * 递归树形部门数据结构
     * @param $data
     * @param $pid
     * @param $level
     * @return array
     */
    protected static function getTreeDepartmentList($data = [], $pid = 0, $level = 0): array
    {
        //$user = EmsUser::pluck('nick_name','id');
        $depTreeList = [];
        foreach ($data as $item) {
            if ($item["parent_id"] == $pid) {
                $item["name"] = $item["name"];
                $item["create_time"] = $item["create_time"];
                $item["order_no"] = $item["order_no"];
                $item["level"] = $level;
                $item["children"] = self::getTreeDepartmentList($data, $item["id"], $level + 1);
                $item["value"] =  $pid . "-" . $item["id"];
                //$item["head_id_txt"] =  $user[$item["head_id"]]??'';
                $head = $item['head'] ?? [];
                $item['head_ids'] = collect($head)->pluck('id');
                unset($item['head']);

                if (empty($item["children"])) {
                    unset($item["children"]);
                }
                $depTreeList[] = $item;
            }
        }
        return $depTreeList;
    }


    /**
     * 获取部门列表
     * @param array $where
     * @return array
     */
    public static function getDepartmentList(array $where=[]): array
    {
        $query  = EmsDepartment::with('head');
        if (isset($where["name"])) {
            $query->where("name", "like", '%' . $where["name"] . '%');
        }

        if (isset($where["status"])) {
            $query->where("status", $where["status"]);
        }

        $list = $query->get()->toArray();

        return self::getTreeDepartmentList($list);
    }
}
