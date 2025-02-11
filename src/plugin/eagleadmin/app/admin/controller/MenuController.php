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

    public function insert(Request $request):Response
    {
        $params = $request->all();
        $params['appid'] = request()->header('appid', 'eagleadmin');
        $this->params = $params;
        return parent::insert($request);
    }

    public function update(Request $request):Response
    {
        $params = $request->all();
        $params['appid'] = request()->header('appid', 'eagleadmin');
        $this->params = $params;
        return parent::update($request);
    }

    public function recyle()
    {

    }

    public function recovery()
    {

    }

    public function realDestroy()
    {

    }

    public function changeStatus()
    {

    }
}
