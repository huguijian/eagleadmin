<?php

namespace plugin\eagleadmin\app\controller\data;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\data\AttachmentLogic;
use support\Request;
use support\Response;

class AttachmentController extends BaseController
{

    private $attachmentLogic;
    public function __construct() 
    {
       $this->attachmentLogic = new AttachmentLogic(); 
    }

    /**
     * 附件列表
     * @param \support\Request $request
     */
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

    /**
     * 删除附件
     * @param \support\Request $request
     */
    public function delete(Request $request)
    {
        $res = $this->attachmentLogic->delete($request);
        return $this->success($res);
    }

}
