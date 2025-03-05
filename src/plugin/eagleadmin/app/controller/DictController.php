<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\logic\DictLogic;
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
}
