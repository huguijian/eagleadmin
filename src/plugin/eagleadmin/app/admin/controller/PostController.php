<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgPost;
use support\Request;
use support\Db;
use support\Response;

/**
 * 岗位管理
 */
class PostController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgPost();
    }

    public function select(Request $request): Response
    {
        $createTime = $request->input('create_time');
        $this->whereArr = [
            ['field'=>'name', 'opt'=>'like', 'val'=>$request->input('name','')],
            ['field'=>'status', 'opt'=>'=', 'val'=>$request->input('status','')]
        ];

        if ($createTime) {
            array_push($this->whereArr,['field'=>'create_time', 'opt'=>'between', 'val'=>[$createTime[0]??'',$createTime[1]??'']]);
        }
        return parent::select($request);
    }

    /**
     * 回收站
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
    {
        $createTime = $request->input('create_time');
        $this->whereArr = [
            ['field'=>'name', 'opt'=>'like', 'val'=>$request->input('name','')],
            ['field'=>'status', 'opt'=>'=', 'val'=>$request->input('status','')]
        ];

        if ($createTime) {
            array_push($this->whereArr,['field'=>'create_time', 'opt'=>'between', 'val'=>[$createTime[0]??'',$createTime[1]??'']]);
        }
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
        $model = $this->selectMap($where,$order);
        $model->onlyTrashed();
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
