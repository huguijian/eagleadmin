<?php
namespace plugin\eagleadmin\app\logic\auth;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgDept;
use plugin\eagleadmin\app\model\EgDeptLeader;
use plugin\eagleadmin\utils\Helper;
class DeptLogic extends ILogic
{
    private $egDeptLeader;
    public function __construct()
    {
        $this->model = new EgDept();
        $this->egDeptLeader = new EgDeptLeader();
    }

    /**
     * 部门列表
     * @param mixed $request
     * @return array
     */
    public function select($request) 
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
     * @param mixed $request
     * @return array
     */
    public function leaders($request)
    {
        $this->model = $this->egDeptLeader;
        $this->withArr = ['userInfo'];
        $this->hasArr[] = ['relation_name'=>'userInfo','where'=>[
            ['user_name','like','%'.$request->input('user_name').'%'],
            ['nick_name','like','%'.$request->input('nick_name').'%']
        ]];
        
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
        return $res;
    }


    /**
     * 删除领导
     * @param mixed $request
     * @param mixed $msg
     * @return bool
     */
    public function delLeader($request,&$msg)
    {
        $ids = $request->input('id');
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_filter($ids);
        if (empty($ids)) {
            $msg = '参数错误';
            return false;
        }
        EgDeptLeader::whereIn('id', $ids)->delete();
        return true;
    }

    /**
     * 添加领导
     * @param mixed $request
     * @return bool
     */
    public function addLeader($request)
    {
        $params = $request->all();
        foreach ($params['users'] as $item) {
            EgDeptLeader::firstOrCreate([
                'dept_id' => $params['dept_id'],
                'user_id' => $item['user_id'],
            ]);
        }
        return true;
    }

    /**
     * 部门回收站
     * @param mixed $request
     * @return array
     */
    public function recycle($request)
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