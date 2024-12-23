<?php

namespace plugin\eagleadmin\app\admin\logic;

use plugin\eagleadmin\app\model\EgMenu;
use plugin\eagleadmin\utils\Helper;
use support\Log;

class UserLogic
{
    /**
     * 获取全部菜单
     */
    public function getAllMenus(): array
    {
        $appid = request()->input('appid', 'eagleadmin');
        $allMenus = EgMenu::where(['type' => ['M','I','L']])
            ->where('appid', $appid)
            ->orderBy('sort', 'desc')
            ->get()
            ->all();
        return Helper::makeArcoMenus($allMenus);
    }

    /**
     * 获取用户有权限的菜单路径列表
     */
    public function getCodes($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles;

        // 获取用户的角色对应的菜单列表
        $menus = $roles ? $roles->pluck('menus') : null;
        // 打散合并
        $menus = $menus ? $menus->collapse() : null;

        // 菜单的路径列表
        $codes = $menus ? $menus->pluck('code') : null;
        $codes = $codes ? $codes->unique()->values()->toArray() : [];
        return $codes;
    }

    /**
     * 获取用户有权限的角色名称列表
     */
    public function getRoles($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles;

        return $roles ? $roles->pluck('name')->toArray() : [];
    }


    /**
     * 获取用户有权限的菜单路由列表
     */
    public function getMenus($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles ?? null;

        // 获取用户的角色对应的菜单列表
        $menus = $roles ? $roles->pluck('menus') : null;
        $menus = $menus ? $menus->toArray() : [];
        $menus = array_merge(...$menus);
        $menus = $menus ? collect($menus)->unique()
            ->values()
            ->toArray() : [];
        return Helper::makeArcoMenus($menus);
    }
}
