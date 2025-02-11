<?php

namespace plugin\eagleadmin\app\admin\controller\auth;

use plugin\eagleadmin\app\admin\logic\auth\MenuLogic;
use plugin\eagleadmin\app\admin\validate\auth\MenuValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsMenu;
use support\exception\BusinessException;
use support\Request;
use support\Response;

class MenuController extends BaseController
{

    protected array $noNeedAuth = ['userMenu'];


    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsMenu();
    }

    /**
     * 菜单列表
     * @throws \Exception
     */
    public function select(Request $request):Response
    {
        $result = MenuLogic::select();
        return $this->success($result);
    }

    /**
     * 获取登录用户对应菜单
     * @param Request $request
     * @return Response
     */
    public function userMenu(Request $request): Response
    {
        $result = MenuLogic::userMenu();
        return $this->success($result);
    }


    /**
     * 新增数据
     * @param Request $request
     * @return Response
     * @throws BusinessException
     * @throws \app\exception\BusinessException
     */
    public function insert(Request $request):Response
    {
        (new MenuValidate())->isPost()->validate();
        $this->callBack = function($params){
            $params['pid'] = $params['pid']??0;
            return $params;
        };
        return parent::insert($request);
    }


    /**
     * 编辑菜单
     * @throws \app\exception\BusinessException
     * @throws BusinessException
     */
    public function update(Request $request):Response
    {
        (new MenuValidate())->validate();
        $this->callBack = function($params){
            $params['pid'] = $params['pid']??0;
            return $params;
        };
        return parent::update($request);
    }

    /**
     * 菜单排序
     * @param Request $request
     * @return Response
     * @throws \app\exception\BusinessException
     */
    public function sortable(Request $request): Response
    {
        $params = (new MenuValidate())->isPost()->validate('SortTable');
        $result = MenuLogic::sortable($params,$msg);
        if (false===$result) {
            return $this->error($msg);
        }
        return $this->success([]);

    }
}