<?php

namespace plugin\eagleadmin\app\admin\controller\auth;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsLog;
use support\Request;
use support\Response;

class AdminLogController extends BaseController
{

    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsLog();
    }

    /**
     * 查询
     * @param Request $request
     * @return \support\Response
     */
    public function select(Request $request):Response
    {
        [$where, $page_size, $order] = $this->selectInput($request);

        $model = $this->selectMap($where,$order);
        $paginator = $model->paginate($page_size);
        return $this->success([
            'list' => $paginator->items(),
            'total' => $paginator->total()
        ], 'ok');

    }
}