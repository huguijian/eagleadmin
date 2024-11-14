<?php

namespace plugin\eagleadmin\app\admin\logic\auth;

use app\model\SsyDeviceAuditGroup;
use app\model\SsyUserCreditScoreLog;
use plugin\eagleadmin\app\model\EgDepartment;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\app\model\EmsDepartment;
use plugin\eagleadmin\app\model\EmsDeviceAuditGroup;
use plugin\eagleadmin\app\model\EmsDeviceGroupUserMap;
use plugin\eagleadmin\app\model\EmsDeviceSampleLog;
use plugin\eagleadmin\app\model\EmsRole;
use plugin\eagleadmin\app\model\EmsUser;
use plugin\eagleadmin\app\model\EmsUserCreditScoreLog;
use plugin\eagleadmin\app\model\EmsUserRole;
use plugin\eagleadmin\app\model\EmsUserSubjectMap;
use plugin\eagleadmin\app\model\EmsUserDepartment;
use support\Db;
use support\Request;

class UserLogic
{
    /**
     * 添加用户
     * @param array $params
     * @param array $tplIds
     * @return bool
     * @throws \Exception
     */
    public static function addUser(array $params = [],&$msg=''): bool
    {
        try {
            Db::beginTransaction();
            $params["password"] = password_hash($params['password'], PASSWORD_BCRYPT, ["cost" => 12]);
            //添加用户

            $userInfo = inputFilter($params,EmsUser::class);
            $userId = EmsUser::insertGetId($userInfo);

            $departmentIds = $params['department_ids'] ?? [];
            $rs = [];
            foreach ($departmentIds as $departmentId) {
                $rs[] = [
                    'department_id' => $departmentId,
                    'user_id' => $userId,
                ];
            }
            EmsUserDepartment::insert($rs);

            //添加用户对应角色
            $userRoles = [];
            foreach($params["role_ids"] as $item){
                $userRoles[] = [
                    "user_id" => $userId,
                    "role_id" => $item
                ];
            }
            EmsUserRole::insert($userRoles);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * 记录信用分变化
     * @param $userId
     * @param $originalCreditScore
     * @param $changeCreditScore
     * @param $handle
     * @return void
     */
    public static function userCreditScoreLog($userId=0,$originalCreditScore=0,$changeCreditScore="",$handle="")
    {

        if ($changeCreditScore<>0) {
            $realCreditScore = eval("return $originalCreditScore$changeCreditScore;");
            EmsUserCreditScoreLog::insert([
                "user_id"               => $userId,
                "real_credit_score"     => $realCreditScore,
                "change_credit_score"   => $changeCreditScore,
                "original_credit_score" => $originalCreditScore,
                "remark" => $handle
            ]);
        }
    }






    /**
     * 编辑用户
     * @param array $params
     * @param $msg
     * @return bool
     */
    public static function editUser(array $params = [],&$msg=''): bool
    {
        try {
            Db::beginTransaction();
            $params["password"] = password_hash($params['password'], PASSWORD_BCRYPT, ["cost" => 12]);
            $userId  = $params["id"];
            $userInfo = inputFilter($params,EmsUser::class);
            if (empty($userInfo['password'])) {
                unset($userInfo['password']);
            }
            EmsUser::where("id",$userId)->update($userInfo);
            EmsUserRole::where("user_id", $userId)->delete();
            //添加用户对应角色
            $userRoles = [];
            foreach($params["role_ids"] as $item){
                $userRoles[] = [
                    "user_id" => $userId,
                    "role_id" => $item
                ];
            }
            EmsUserRole::insert($userRoles);


            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }

        return true;
    }


    /**
     * 删除用户
     * @param string $userIds
     * @return bool
     * @throws \Exception
     */
    public static function deleteUsers(array $userIds = [],&$msg=''): bool
    {
        try {
            Db::beginTransaction();
            EmsUser::whereIn("id",$userIds)->delete();
            EmsUserRole::whereIn("user_id", $userIds)->delete();
            Db::commit();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * 获取用户信息
     * @param string $userId
     * @param string $msg
     * @return bool|array
     */
    public static function getUserInfo(string $userId = "",&$msg=''): bool|array
    {
        try {
            $userInfo = EgUser::where('id', $userId)->first();
            if (!empty($userInfo)) {
                $departmentNameModel = EgDepartment::query()->where('id', $userInfo["department_id"])->first();
                $role_ids = Db::table('eg_user_role')->where("user_id", $userId)->pluck('role_id');
                $roleName = EgRole::whereIn("id", $role_ids)->pluck("name", "id");
                $roleName = array_values(collect($roleName)->toArray());
                $userInfo["roleIdList"] = $role_ids;
                $userInfo["role_id"]    = collect($role_ids)->toArray();
                $userInfo["role_name"]  = $roleName;
                $userInfo["departmentName"] = $departmentNameModel["name"] ?? "";
            }
            $userInfo = collect($userInfo)->toArray();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return false;
        }

        return $userInfo;
    }


    public static function changePw($userId=0,$oldPw='',$newPw='',&$msg=''): bool
    {
        $userInfo = EmsUser::where("id",$userId)->first();
        if (!password_verify($oldPw, $userInfo["password"])) {
            $msg = '当前密码有误';
            return false;
        }

        $newPw = password_hash($newPw,PASSWORD_BCRYPT,['cost'=>12]);
        $rows = EmsUser::where("id", $userInfo["id"])->update([
            "password" => $newPw
        ]);

        return $rows>0;
    }

    /**
     * 获取用户Id对应角色信息
     * @param $userIds
     * @return array
     */
    public static function getRoleListByUserIds($userIds = []): array
    {

        $userInfo = EmsUserRole::whereIn("user_id", $userIds)->get();
        $roleList = EmsRole::pluck("name", "id");
        $newArr   = [];
        foreach ($userInfo as $item) {
            $newArr[$item["user_id"]]["name"][] = $roleList[$item["role_id"]] ?? "";
            $newArr[$item["user_id"]]["role_ids"][] = $item["role_id"];
        }

        return $newArr;
    }
}
