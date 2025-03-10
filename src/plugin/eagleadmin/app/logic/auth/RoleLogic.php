<?php
namespace plugin\eagleadmin\app\logic\auth;
use plugin\eagleadmin\app\logic\ILogic;
use support\Db;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\model\EgRoleDept;
use plugin\eagleadmin\utils\Helper;
use plugin\eagleadmin\app\model\EgRoleMenu;
class RoleLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgRole();
    }

    /**
     * 角色列表
     * @param mixed $request
     * @return array
     */
    public function select($request) 
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
     * 获取角色对应的菜单
     * @return array{id: mixed|null, menus: mixed}
     */
    public function getMenuByRole()
    {
        $id = request()->input('id');
        $role = EgRole::where('id', $id)->first();
        $menus = $role['menus'] ?? [];
        return [
            'id' => $id,
            'menus' => $menus,
        ];
    }

    /**
     * 获取角色对应的部门
     * @return array{depts: mixed, id: mixed|null}
     */
    public function getDeptByRole()
    {
        $id = request()->input('id');
        $role = EgRole::where('id', $id)->first();
        $depts = $role['depts'] ?? [];
        return [
            'id' => $id,
            'depts' => $depts,
        ];
    }

    /**
     * 菜单权限
     * @param mixed $request
     * @return bool
     */
    public function updateMenuPermission($request)
    {
        $id = $request->input('id');
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
        return true;
    }


    /**
     * 数据权限
     * @param mixed $request
     * @return bool
     */
    public function updateDataPermission($request)
    {
        $id = $request->input('id');
        $dataScope = $request->input('data_scope');
        try {
            Db::beginTransaction();
            EgRole::where('id',$id)->update(['data_scope' => $dataScope]);
            if ($dataScope == '2') {
                EgRoleDept::where('role_id', $id)->delete();
                $roleDept = [];
                foreach ($request->input('dept_ids') as $deptId) {
                    $roleDept[] = [
                        'role_id'=> $id,
                        'dept_id'=> $deptId,
                    ];
                }
                EgRoleDept::insert($roleDept);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
        return true;
    }


    /**
     * 更新角色信息
     * @param mixed $request
     * @return array{row: bool|mixed}
     */
    public function update($request)
    {
        return parent::update($request);
    }

    /**
     * 角色回收站
     * @param mixed $request
     * @return array
     */
    public function recycle($request)
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
        return $res;
    }
}