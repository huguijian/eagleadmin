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

    /**
     * 角色列表
     * @param Request $request
     * @return Response
     */
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

        $this->whereArr = [
            [
                'field' => 'name',
                'opt' => 'like',
                'val' => $request->input('name')
            ],
            [
                'field' => 'code',
                'opt' => '=',
                'val' => $request->input('code')
            ]
        ];

        return parent::select($request);
    }


    /**
     * 通过角色获取菜单
     * @return Response
     */
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


    /**
     * 角色回收站
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
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

        $this->whereArr = [
            [
                'field' => 'name',
                'opt' => 'like',
                'val' => $request->input('name')
            ],
            [
                'field' => 'code',
                'opt' => '=',
                'val' => $request->input('code')
            ]
        ];


        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
        $model = $this->selectMap($where,$order);

        $model->onlyTrashed();
        if ($this->pageSize == -1) { // 值为-1表示不分页
            $list = $model->get() ?? [];
        } else {
            $pageSize = $this->pageSize > 0 ? $this->pageSize : $pageSize;
            $paginator = $model->paginate($pageSize);
            $list = $paginator->items() ?? [];
            $res['total'] = $paginator->total();
        }
        if ($this->callBack && is_callable($this->callBack)) {
            $list = call_user_func($this->callBack, $list) ?? [];
        }
        $res['items'] = $list;
        return $this->success($res, 'ok');
    }


    /**
     * 恢复角色
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgRole::whereIn('id',$id)->restore();
        return $this->success([],'恢复成功');
    }

    /**
     * 销毁角色
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        EgRole::whereIn('id',$id)->forceDelete();
        return $this->success([]);
    }
}
