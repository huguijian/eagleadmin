<?php
namespace plugin\eagleadmin\app\common;


use plugin\eagleadmin\app\model\Admin;
use plugin\eagleadmin\app\model\AdminRole;
use plugin\eagleadmin\app\model\EmsRole;
use plugin\eagleadmin\app\model\EmsUserRole;
use plugin\eagleadmin\app\model\Role;
use plugin\eagleadmin\app\model\Rule;

class Auth
{
    /**
     * 获取权限范围内的所有角色id
     * @param bool $with_self
     * @return array
     */
    public static function getScopeRoleIds(bool $with_self = false): array
    {
        if (!$admin = admin()) {
            return [];
        }
        $role_ids = $admin['roles'];
        $rules = EmsRole::whereIn('id', $role_ids)->pluck('rules')->toArray();
        if ($rules && in_array('*', $rules)) {
            return EmsRole::pluck('id')->toArray();
        }

        $roles = EmsRole::get();
        $tree = new Tree($roles);
        $descendants = $tree->getDescendant($role_ids, $with_self);
        return array_column($descendants, 'id');
    }

    /**
     * 获取权限范围内的所有管理员id
     * @param bool $with_self
     * @return array
     */
    public static function getScopeAdminIds(bool $with_self = false): array
    {
        $role_ids = static::getScopeRoleIds();
        $admin_ids = EmsUserRole::whereIn('role_id', $role_ids)->pluck('user_id')->toArray();
        if ($with_self) {
            $admin_ids[] = admin_id();
        }
        return array_unique($admin_ids);
    }

    /**
     * 是否是超级管理员
     * @param int $admin_id
     * @return bool
     */
    public static function isSupperAdmin(int $admin_id = 0): bool
    {
        if (!$admin_id) {
            if (!$roles = admin('roles')) {
                return false;
            }
        } else {
            $roles = EmsUserRole::where('user_id', $admin_id)->pluck('role_id');
        }
        $rules = EmsRole::whereIn('id', $roles)->pluck('rules');
        return $rules && in_array('*', $rules->toArray());
    }
}
