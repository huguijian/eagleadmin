<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgDept;
use plugin\eagleadmin\app\model\EgDeptLeader;
use support\Request;
use support\Response;
use plugin\eagleadmin\utils\Helper;

class DeptController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgDept();
    }

    public function select(Request $request) :Response
    {
        $this->callBack = function($data) {
            $data = collect($data)->map(function($item){
                    $item['label'] = $item['name'];
                    $item['value'] = $item['id'];
                    return $item;
                })
                ->toArray();
            return Helper::makeTree($data);
        };

        $createTime = $request->get('create_time','');
        $this->whereArr = [
            ['field'=>'name','opt'=>'like','val'=>$request->input('name')]
        ];

        if ($createTime) {
            $this->whereArr = array_merge($this->whereArr,[
                ['field'=>'create_time','opt'=>'between','val'=>[$createTime[0],$createTime[1]]]
            ]);
        }
        return parent::select($request);
    }


    /**
     * 领导列表
     * @param Request $request
     * @return Response
     */
    public function leaders(Request $request)
    {
        $this->model = new EgDeptLeader();
        $this->withArr = ['userInfo'];
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';

        $where[] = ['field'=>'dept_id','opt'=>'=','val'=>$request->input('dept_id')];
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
     * 删除领导列表
     * @param Request $request
     * @return Response
     */
    public function delLeader(Request $request)
    {
        $ids = $request->input('id');
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_filter($ids);
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $res = EgDeptLeader::whereIn('id', $ids)->delete();
        return $this->success($res, '删除成功');
    }


    /**
     * 添加部门领导
     * @param Request $request
     * @return Response
     */
    public function addLeader(Request $request)
    {
        $params = $request->all();
        foreach ($params['users'] as $item) {
            EgDeptLeader::firstOrCreate([
                'dept_id' => $params['dept_id'],
                'user_id' => $item['user_id'],
            ]);
        }
        return $this->success([]);
    }
}
