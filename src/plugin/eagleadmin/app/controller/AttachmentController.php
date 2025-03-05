<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\AttachmentLogic;
use support\Request;
use support\Response;

class AttachmentController extends BaseController
{

    private $attachmentLogic;
    public function __construct() 
    {
       $this->attachmentLogic = new AttachmentLogic(); 
    }

    public function select(Request $request)
    {

        $res = $this->attachmentLogic->select($request);
        return $this->success($res);
      
    }


    /**
     * 下载文件
     * @param Request $request
     * @return Response|\Webman\Http\Response
     */
    public function downloadById(Request $request)
    {
        $id = $request->get('id');
        $info = $this->attachmentLogic->where('id',$id)->first();
        return response()->download(public_path($info['path']));
    }
}
