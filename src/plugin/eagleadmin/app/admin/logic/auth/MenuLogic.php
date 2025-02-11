<?php

namespace plugin\eagleadmin\app\admin\logic\auth;

use plugin\eagleadmin\app\model\EmsMenu;
use plugin\eagleadmin\app\model\EmsRole;
use plugin\eagleadmin\app\model\EmsUserRole;
use support\Db;
use Tinywan\Jwt\JwtToken;
use tools\Tree;

class MenuLogic
{

    /**
     * 菜单列表
     * @return array
     */
    public static function select(): array
    {
        $menus = EmsMenu::orderBy("weigh","asc")->whereIn("type",['menu','menu_dir','button'])->get();
        $menus = collect($menus)->toArray();
        foreach ($menus as &$item) {
            $item['meta'] = [
                'name' => $item['name'],
                'icon'  => $item['icon']
            ];
        }
        if (isset($params["select"]) && $params["select"]=="true") {
            $tree = Tree::instance();
            $menus = $tree->assembleChild($menus);
            $menus = $tree->getTreeArray($menus,'name');
            $menus = $tree->assembleTree($menus);

        }else{
            $menus = Tree::instance()->assembleChild($menus);
        }

        return $menus;
    }

    /**
     * 获取登录用户所拥有菜单节点
     * @return array
     */
    public static function userMenu(): array
    {
        $accessUserId = JwtToken::getCurrentId();
        $roleIds = EmsUserRole::where("user_id",$accessUserId)->pluck("role_id");
        $rules   = EmsRole::whereIn("id",$roleIds)->pluck("rules");
        $rules   = collect($rules)->toArray();

        $rulesArr = [];
        foreach ($rules as $item) {
            $rulesArr = array_merge($rulesArr,explode(",",$item));
        }
        // 基础权限必须要有
        $mustArr = [44, 45, 46, 47, 64, 65, 66, 140];
        foreach ($mustArr as $must) {
            if (!in_array($must, $rulesArr)) {
                array_push($rulesArr, $must);
            }
        }
        // 超级管理员
        if (in_array('*', $rulesArr)){
            $menus = EmsMenu::orderBy("weigh","asc")
                ->whereIn("type",['menu','menu_dir'])
                ->get();
        }else{
            $menus = EmsMenu::orderBy("weigh","asc")
                ->whereIn("type",['menu','menu_dir'])
                ->whereIn("id",$rulesArr)
                ->get();
        }

        $menus = collect($menus)->toArray();
        foreach ($menus as &$item) {
            $item['meta'] = [
                'name' => $item['name'],
                'icon'  => $item['icon']
            ];
        }
        return Tree::instance()->assembleChild($menus);
    }


    /**
     * 菜单排序
     * @param array $params
     * @param string $msg
     * @return bool
     */
    public static function sortable(array $params = [], &$msg=''): bool
    {
        try {
            $msg = '';
            Db::beginTransaction();
            $currMenu   = EmsMenu::where("id",$params["id"])->first();
            $targetMenu = EmsMenu::where("id",$params["target_id"])->first();
            if ($currMenu["weigh"]==$targetMenu["weigh"]) {
                $list = EmsMenu::get();
                foreach ($list as $item) {
                    EmsMenu::where("id",$item["id"])->update([
                        "weigh" => $item["id"]
                    ]);
                }
            }

            EmsMenu::where("id",$params["id"])->update([
                "weigh" => $targetMenu["weigh"]
            ]);

            EmsMenu::where("id",$params["target_id"])->update([
                "weigh" => $currMenu["weigh"]
            ]);
            Db::commit();
        }catch (\Exception $exception) {
            Db::rollBack();
            $msg = $exception->getMessage();
            return false;
        }
        return true;
    }
}
