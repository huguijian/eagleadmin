<?php

namespace plugin\eagleadmin\app\admin\controller\auth;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\constant\Constant;
use plugin\eagleadmin\app\model\EmsLog;
use plugin\eagleadmin\app\model\EmsUser;
use plugin\eagleadmin\app\model\EmsUserOrder;
use support\Request;
use support\Response;

class LogController extends BaseController
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsLog();
    }

    /**
     * 操作日志列表
     * @param Request $request
     * @return Response
     */
    public function select(Request $request): Response
    {

        $this->withArr = ['userInfo'];
        $search = $request->input('search');
        [$where, $pageSize, $order] = $this->selectInput($request);
        if (!empty($search)) {
            foreach ($search as $item) {
                if ($item['field']=='user_name' && !empty($item['val'])) {
                    $this->hasArr = [
                        [
                            'relation_name'=>'userInfo',
                            'where'=>[
                                ['user_name','like','%'.$item['val'].'%']
                            ]
                        ],
                    ];
                }
            }
        }

        $model = $this->selectMap($where,$order);
        if ($this->pageSize == -1) { // 值为-1表示不分页
            $list = $model->get() ?? [];
        } else {
            $pageSize = $this->pageSize > 0 ? $this->pageSize : $pageSize;
            $paginator = $model->paginate($pageSize);
            $list = $paginator->items() ?? [];
            $res['total'] = $paginator->total();
        }
        if ($this->callBack && is_callable($this->callBack)) {
            $list = call_user_func($this->callBack, $list) ?? [];
        }
        $res['items'] = $list;
        return $this->success($res, 'ok');
    }

    /**
     * 操作日志详情
     * @param Request $request
     * @return Response
     */
    public function detail(Request $request)
    {
        $params = $request->all();
        $info = EmsLog::where('id',$params['id'])->first();
        return $this->success($info);
    }

}