<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgDictCategory;
use support\Request;
use support\Db;
use support\Response;

class DictCategoryController extends BaseController
{
    protected $model;

    protected $noNeedAuth = ['dictAll'];

    public function __construct() {
        $this->model = new EgDictCategory();
    }

    public function select(Request $request): Response
    {
        $this->whereArr = [
            ['field'=>'name','opt'=>'like','val'=>$request->input('name')],
            ['field'=>'code','opt'=>'like','val'=>$request->input('code')],
            ['field'=>'status','opt'=>'like','val'=>$request->input('status')],
        ];
        return parent::select($request);
    }

    public function data(Request $request)
    {
        $code = $request->input('code');
        $category = EgDictCategory::where('code', $code)->first();
        $data = $category->dict ?? [];
        return $this->success($data);
    }


    /**
     * 字典数据
     * @param Request $request
     * @return Response
     */
    public function dictAll(Request $request)
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
        return $this->success($res, '查询成功！');
    }

    /**
     * 执行软删除
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        EgDictCategory::whereIn('id', $request->input('id'))->delete();
        return $this->success('删除成功！');
    }


    /**
     * 显示回收站信息
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
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
        return $this->success($res, 'ok');
    }


    /**
     * 销毁
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $res = EgDictCategory::whereIn('id',$request->input('id'))->forceDelete();
        return $this->success($res);
    }

    /**
     * 恢复软删除的数据
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        EgDictCategory::whereIn('id',$request->input('id'))->restore();
        return $this->success([]);
    }
}
