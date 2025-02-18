<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgAttachment;
use support\Request;
use support\Db;
use support\Response;

class AttachmentController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgAttachment();
    }

    public function select(Request $request): Response
    {
        return parent::select($request);
    }


    /**
     * 下载文件
     * @param Request $request
     * @return Response|\Webman\Http\Response
     */
    public function downloadById(Request $request)
    {
        $id = $request->get('id');
        $info = EgAttachment::where('id',$id)->first();
        return response()->download(public_path($info['path']));
    }
}
