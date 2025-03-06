<?php

namespace plugin\eagleadmin\app\controller\auth;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\auth\PostLogic;
use plugin\eagleadmin\app\model\EgPost;
use support\Request;
use support\Response;
/**
 * 岗位管理
 */
class PostController extends BaseController
{

    private $postLogic;
    
    public function __construct() 
    {
        $this->postLogic = new PostLogic();
    }

    /**
     * 岗位列表
     * @param \support\Request $request
     * @return \support\Response
     */
    public function select(Request $request): Response
    {
        $res = $this->postLogic->select($request);
        return $this->success($res);
    }


    /**
     * 添加岗位
     * @param \support\Request $request
     */
    public function insert(Request $request)
    {
        $res = $this->postLogic->insert($request);
        return $this->success($res,'添加成功');
    }

    /**
     * 修改岗位
     * @param \support\Request $request
     */
    public function update(Request $request)
    {
        $res = $this->postLogic->update($request);
        return $this->success($res,'修改成功');
    }
    
    /**
     * 删除岗位
     * @param \support\Request $request
     */
    public function delete(Request $request)
    {
        $res = $this->postLogic->delete($request);
        return $this->success($res,'删除成功');
    }
    /**
     * 回收站
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
    {
        $res = $this->postLogic->recycle($request);
        return $this->success($res);
    }

    /**
     * 恢复
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgPost::whereIn('id',$id)->restore();
        return $this->success([],'ok');
    }

    /**
     * 销毁
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        EgPost::whereIn('id',$id)->forceDelete();
        return $this->success([],'ok');
    }


}
