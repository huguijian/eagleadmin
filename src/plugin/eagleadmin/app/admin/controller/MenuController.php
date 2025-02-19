<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\admin\logic\MenuLogic;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgMenu;
use support\Request;
use support\Response;

class MenuController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgMenu();
    }

    public function index()
    {
        $search = $this->inputFilter(request()->all());
        $res = (new MenuLogic())->menu($search);
        return $this->success($res, '查询成功！');
    }

    public function delete(Request $request):Response
    {
        $ids = $request->input('id');
        $res = EgMenu::whereIn('id', $ids)->delete();
        if ($res) {
            return $this->success([], '删除成功！');
        }
        return $this->error('删除失败！');
    }
    /**
     * 添加菜单
     * @param Request $request
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function insert(Request $request):Response
    {
        $data = $this->insertInput($request);
        $data['parent_id'] = empty($data['parent_id']) ? 0 : $data['parent_id'];
        $id   = $this->doInsert($data);
        if ($id) {
            return $this->success(["id"=>$id]);
        }
        return $this->error('保存失败!');
    }

    public function update(Request $request):Response
    {
        return parent::update($request);
    }

    /**
     * 回收站
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function recycle()
    {
        $search = $this->inputFilter(request()->all());
        $res = (new MenuLogic())->menu($search,true);
        return $this->success($res, '查询成功！');
    }

    /**
     * 恢复
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgMenu::whereIn('id',$id)->restore();
        return $this->success([], '恢复成功！');
    }

    /**
     * 销毁
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        EgMenu::whereIn('id',$id)->forceDelete();
        return $this->success([], '删除成功！');
    }

}
