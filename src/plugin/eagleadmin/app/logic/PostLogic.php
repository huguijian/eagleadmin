<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\model\EgPost;

class PostLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgPost();
    }

    public function select($request) 
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

    public function recycle($request)
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
        return $res;
    }
}
