<?php

namespace plugin\eagleadmin\app\admin\logic\auth;

use plugin\eagleadmin\app\model\EmsRole;
use plugin\eagleadmin\app\service\SysUserService;
use tools\Tree;

class RoleLogic
{
    /**
     * 数据列表
     * @return array
     */
    public static function select(): array
    {
        $roles = EmsRole::get();
        $roles = collect($roles)->toArray();
        foreach ($roles as &$item) {
            $item['rules'] = explode(',',$item['rules']);
        }
        if (isset($params["select"]) && $params["select"]=="true") {
            $tree = Tree::instance();
            $roles = $tree->assembleChild($roles);
            $roles = $tree->getTreeArray($roles);
            $roles = $tree->assembleTree($roles);
        }else{
            $roles = self::getTreeRole($roles);
        }

        return $roles;
    }


    /**
     * 添加角色
     * @param array $params
     * @return int
     */
    public static function addRole(array $params=[]): int
    {
        return EmsRole::insertGetId([
            "pid"   => $params["pid"]??0,
            "name"  => $params["name"],
            "role_val" => $params['role_val']??"",
            "remark" => $params['remark']??"",
            "rules" => implode(",",$params["rules"]),
            "status" => $params["status"],
        ]);
    }

    /**
     * 编辑角色
     * @param array $params
     * @return int
     */
    public static function editRole(array $params=[]): int
    {
        return EmsRole::where("id",$params["id"])->update([
            "pid"   => $params["pid"]??0,
            "role_val" => $params['role_val']??"",
            "remark" => $params['remark']??"",
            "name"  => $params["name"],
            "rules" => implode(",",$params["rules"]),
            "status" => $params["status"],
        ]);
    }

    /**
     * 获取角色树形菜单
     * @param $data
     * @param $pid
     * @param $level
     * @return array
     */
    public static function getTreeRole($data, $pid = 0, $level = 0)
    {
        $newArr = [];
        foreach ($data as $item) {
            if ($item["pid"] == $pid) {
                $item["children"] = self::getTreeMenuNormal($data, $item["id"], $level + 1);
                $newArr[] = $item;
            }
        }

        return $newArr;
    }

    /**
     * 递归树形菜单
     * @param $data
     * @param int $pid
     * @param int $level
     * @return array
     */
    public static function getTreeMenuNormal($data, $pid = 0, $level = 0)
    {
        $newArr = [];
        foreach ($data as $item) {
            if ($item["pid"] == $pid) {
                $item["children"] = self::getTreeMenuNormal($data, $item["id"], $level + 1);
                $newArr[] = $item;
            }
        }

        return $newArr;
    }
}