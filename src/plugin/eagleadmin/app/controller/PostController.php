<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\PostLogic;
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

    public function select(Request $request): Response
    {
        $res = $this->postLogic->select($request);
        return $this->success($res);
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
