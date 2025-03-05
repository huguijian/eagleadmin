<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\model\EgDictCategory;

class DictCategoryLogic extends ILogic
{
    private $egDictCategory;
    public function __construct()
    {

        $this->egDictCategory = new EgDictCategory();
    }


    public function select($request)
    {
        $this->whereArr = [
            ['field'=>'name','opt'=>'like','val'=>$request->input('name')],
            ['field'=>'code','opt'=>'like','val'=>$request->input('code')],
            ['field'=>'status','opt'=>'like','val'=>$request->input('status')],
        ];
        return parent::select($request);
    }

    public function data($request)
    {
        $code = $request->input('code');
        $category = EgDictCategory::where('code', $code)->first();
        $data = $category->dict ?? [];
        return $data;
    }

    public function dictAll()
    {
        $dcs = EgDictCategory::where('status', 1)->get();
        $res = [];
        foreach($dcs as $dc) {
            $dict = optional($dc['dict'])->toArray();
            foreach($dict as $key => $val) {
                $dict[$key]['label'] = $val['dict_name'];
                $dict[$key]['value'] = $val['dict_value'];
            }
            $res[$dc['code']] = $dict;
        }
        return $res;
    }


    /**
     * 显示回收站信息
     * @param mixed $request
     * @return void
     */
    public function recycle($request)   
    {
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
        $model = $this->selectMap($where,$order);

        //显示软删的数据
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
