<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\DictCategoryLogic;
use plugin\eagleadmin\app\model\EgDictCategory;
use support\Request;
use support\Response;

class DictCategoryController extends BaseController
{

    protected $noNeedAuth = ['dictAll'];

    private $dictCategoryLogic;
    public function __construct() {
        $this->dictCategoryLogic = new DictCategoryLogic();
    }

    public function select(Request $request): Response
    {
        $res = $this->dictCategoryLogic->select($request);
        return $this->success($res);
    }

    public function data(Request $request)
    {
       
        $data = $this->dictCategoryLogic->data($request);
        return $this->success($data);
    }


    /**
     * 字典数据
     * @param Request $request
     * @return Response
     */
    public function dictAll(Request $request)
    {
        $res = $this->dictCategoryLogic->dictAll();
        return $this->success($res, '查询成功！');
    }

    /**
     * 执行软删除
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        EgDictCategory::whereIn('id', $request->input('id'))->delete();
        return $this->success([],'删除成功！');
    }


    /**
     * 显示回收站信息
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
    {
        $res = $this->dictCategoryLogic->recycle($request);
        return $this->success($res, 'ok');
    }


    /**
     * 销毁
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $res = EgDictCategory::whereIn('id',$request->input('id'))->forceDelete();
        return $this->success($res);
    }

    /**
     * 恢复软删除的数据
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        EgDictCategory::whereIn('id',$request->input('id'))->restore();
        return $this->success([]);
    }
}
