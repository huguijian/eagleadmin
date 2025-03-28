<?php

namespace plugin\eagleadmin\app\controller\data;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\data\DictCategoryLogic;
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

    /**
     * 字典分类列表
     * @param \support\Request $request
     * @return \support\Response
     */
    public function select(Request $request): Response
    {
        $res = $this->dictCategoryLogic->select($request);
        return $this->success($res);
    }


    


    /**
     * 添加字典分类
     * @param \support\Request $request
     * @return \support\Response
     */
    public function insert(Request $request): Response
    {
        $res = $this->dictCategoryLogic->insert($request);
        return $this->success($res,'添加成功!');
    }


    /**
     * 修改字典分类
     * @param \support\Request $request
     * @return \support\Response
     */
    public function update(Request $request): Response
    {
        $res = $this->dictCategoryLogic->update($request);
        return $this->success($res,'修改成功!');
    }

    /**
     * 字典数据
     * @param \support\Request $request
     */
    public function data(Request $request)
    {
        $data = $this->dictCategoryLogic->data($request);
        return $this->success($data);
    }

    /**
     * 修改字典分类状态
     * @param \support\Request $request
     */
    public function changeStatus(Request $request)
    {
        $data = $this->dictCategoryLogic->update($request);
        return $this->success($data,'修改成功！');
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
