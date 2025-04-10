<?php
namespace plugin\eagleadmin\app\logic\data;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgDictCategory;
use support\Db;
use plugin\eagleadmin\app\model\EgDict;

class DictCategoryLogic extends ILogic
{
    public function __construct()
    {

        $this->model = new EgDictCategory();
    }


    /**
     * 字典分类列表
     * @param mixed $request
     * @return array
     */
    public function select($request)
    {
        $this->whereArr = [
            ['field'=>'name','opt'=>'like','val'=>$request->input('name')],
            ['field'=>'code','opt'=>'like','val'=>$request->input('code')],
            ['field'=>'status','opt'=>'like','val'=>$request->input('status')],
        ];
        return parent::select($request);
    }

     /**
     * 更新字典分类
     * @param mixed $request
     * @return bool|int
     */
    public function update($request)
    {
        try {
            Db::beginTransaction();
            $params = $request->all();
            $id = $request->input('id');
            $res = EgDictCategory::where('id', $id)->update($params);
            EgDict::where('category_id', $id)->update([
                'dict_code' => $params['dict_code'],
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }
        return $res;
    }

    /**
     * 字典数据
     * @param mixed $request
     */
    public function data($request)
    {
        $code = $request->input('code');
        $category = EgDictCategory::where('dict_code', $code)->first();
        $data = $category->dict ?? [];
        return $data;
    }

    /**
     * 字典所有数据
     * @return array
     */
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
            $res[$dc['dict_code']] = $dict;
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
