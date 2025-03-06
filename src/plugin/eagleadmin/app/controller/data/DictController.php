<?php

namespace plugin\eagleadmin\app\controller\data;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\logic\data\DictLogic;
class DictController extends BaseController
{

    private $dictLogic;
    public function __construct() 
    {
        $this->dictLogic = new DictLogic();
        
    }

    public function select(Request $request): Response
    {
        $res = $this->dictLogic->select($request);
        return $this->success($res);
    }

    /**
     * 添加字典数据
     * @param \support\Request $request
     * @return \support\Response
     */
    public function insert(Request $request): Response
    {
        $res = $this->dictLogic->insert($request);
        return $this->success($res,'添加成功');
    }

    /**
     * 修改字典数据
     * @param \support\Request $request
     * @return \support\Response
     */
    public function update(Request $request): Response
    {   
        $res = $this->dictLogic->update($request);
        return $this->success($res,'修改成功');
    }

    /**
     * 删除成功
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = $request->input('id');
        $this->dictLogic->whereIn('id',$id)->forceDelete($id);
        return $this->success([]);
    }


    /**
     * 修改字典数据状态
     * @param \support\Request $request
     * @return \support\Response
     */
    public function changeStatus(Request $request): Response
    {
        $res = $this->dictLogic->update($request);
        return $this->success([],'修改成功');
    }
}
