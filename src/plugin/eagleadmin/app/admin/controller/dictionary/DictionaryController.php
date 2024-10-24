<?php

namespace plugin\eagleadmin\app\admin\controller\dictionary;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsDict;
use plugin\eagleadmin\app\model\EmsDictCategory;
use support\Request;

class DictionaryController extends BaseController
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsDictCategory();
    }

    /**
     * 获取字典列表
     * @param Request $request
     * @return \support\Response
     */
    public function getDictItems(Request $request)
    {
        $params = $request->all();
        $list = EmsDict::where('dict_code',$params['dict_code'])->get();
        return $this->success($list);
    }


    public function dictAll(Request $request)
    {

    }
}