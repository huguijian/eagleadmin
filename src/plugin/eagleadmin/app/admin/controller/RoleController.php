<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Db;
use support\Response;
use plugin\eagleadmin\app\admin\logic\UserLogic;
use plugin\eagleadmin\app\model\EgDepartment;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\model\EgRoleMenu;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\utils\Helper;

class RoleController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgRole();
    }

    public function select(Request $request) :Response
    {
        $this->callBack = function($data) {
            $data = collect($data)->map(function($item){
                    $item['label'] = $item['name'];
                    $item['value'] = $item['id'];
                    return $item;
                })
                ->toArray();
            return Helper::makeTree($data);
        };
        return parent::select($request);
    }


    public function getMenuByRole()
    {
        $id = request()->input('id');
        $role = EgRole::where('id', $id)->first();
        $menus = $role['menus'] ?? [];
        return $this->success([
            'id' => $id,
            'menus' => $menus,
        ]);
    }

    public function getDeptByRole()
    {
        $id = request()->input('id');
        $role = EgRole::where('id', $id)->first();
        $depts = $role['depts'] ?? [];
        return $this->success([
            'id' => $id,
            'depts' => $depts,
        ]);
    }

    public function updateMenuPermission($id)
    {
        $id = request()->input('id');
        try {
            Db::beginTransaction();
            EgRoleMenu::where('role_id', $id)->delete();
            $menuIds = request()->input('menu_ids');
            $data = [];
            foreach($menuIds as $menuId) {
                $data[] = [
                    'menu_id' => $menuId,
                    'role_id' => $id,
                ];
            }
            EgRoleMenu::insert($data);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
        return $this->success([], '保存成功！');
    }

    public function updateDataPermission($id)
    {
        $id = request()->input('id');
        try {
            Db::beginTransaction();

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
        return $this->success([], '保存成功！');
    }
}
