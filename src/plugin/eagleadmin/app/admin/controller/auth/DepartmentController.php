<?php
namespace plugin\eagleadmin\app\admin\controller\auth;
use plugin\eagleadmin\app\admin\logic\auth\DepartmentLogic;
use plugin\eagleadmin\app\admin\validate\auth\DepartmentValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsDepartment;
use plugin\eagleadmin\app\model\EmsDepartmentHead;
use plugin\eagleadmin\app\model\EmsUser;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use support\Db;

class DepartmentController extends BaseController
{
    protected $model = null;

    /**
     * 增删改查
     */
    public function __construct()
    {
        $this->model = new EmsDepartment();
    }

    /**
     * 新增部门
     * @param Request $request
     * @return Response
     * @throws BusinessException
     * @throws \app\exception\BusinessException
     */
    public function insert(Request $request):Response
    {
        (new DepartmentValidate())->isPost()->validate('insert');
        $params = $request->all();
        $headIds = $params['head_ids'] ?? [];
        try {
            Db::beginTransaction();
            $params['parent_id'] = empty($params['parent_id'])?0:$params['parent_id'];
            $params['order_no'] = empty($params['order_no'])?0:$params['order_no'];
            $res = EmsDepartment::create($params);
            $rs = [];
            foreach ($headIds as $headId) {
                $rs[] = [
                    'department_id' => $res['id'],
                    'head_id' => $headId,
                ];
            }
            EmsDepartmentHead::insert($rs);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        success($res);
    }

    /**
     * 更新数据
     * @param Request $request
     * @return Response
     * @throws BusinessException
     * @throws \app\exception\BusinessException
     */
    public function update(Request $request): Response
    {
        (new DepartmentValidate())->validate('update');
        $id = $request->input('id');
        $headIds = $request->input('head_ids', []);
        $rs = [];
        foreach($headIds as $headId) {
            $rs[] = [
                'department_id' => $id,
                'head_id' => $headId,
            ];
        }
        try {
            Db::beginTransaction();
            // 先清空原有的
            EmsDepartmentHead::where('department_id', $id)
                ->delete();
            // 插入新的
            EmsDepartmentHead::insert($rs);

            $this->callBack = function($params){
                $params['parent_id']= $params['parent_id']??0;
                $params['order_no'] = $params['order_no']??0;
                return $params;
            };
            $res = parent::update($request);
            Db::commit();
        } catch(\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return $res;
    }

    /**
     * 删除部门
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $exists = EmsUser::whereIn('department_id',$ids)->exists();
        if (false!==$exists) {
            return $this->error('请先删除部门下的用户');
        }

        $res = $this->doDelete($ids);
        if ($res) {
            return $this->success([]);
        } else {
            return $this->error('删除失败!');
        }
    }

    /**
     * 部门列表
     */
    public function select(Request $request):Response
    {
        $params = $request->all();
        $result = DepartmentLogic::getDepartmentList($params);
        return $this->success($result);
    }

}
