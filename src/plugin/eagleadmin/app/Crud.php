<?php

namespace plugin\eagleadmin\app;

use plugin\eagleadmin\app\common\Auth;
use support\Db;
use support\exception\BusinessException;
use support\Model;
use support\Request;

trait Crud
{
    /**
     * @var Model
     */
    protected $model = null;

    /**
     * 追加的入参
     *
     * @var [array]
     */
    protected $params = [];

    /**
     * 自定义附加查询条件
     *
     * @var [type]
     */
    protected $whereArr = [];

    /**
     * 自定义查询字段
     *
     * @var [type]
     */
    protected $selectArr = null;

    /**
     * with
     *
     * @var [Array]
     */
    protected $withArr = [];

    /**
     * 查询结果回调处理
     * @var [Object]
     */
    protected $callBack = null;

    /**
     * 关联关系搜索条件
     *
     * @var array
     */
    protected $hasArr = [];

    /**
     * 自定义分页数
     */
    protected $pageSize = null;

    /**
     * 数据限制
     * 例如当$dataLimit='personal'时将只返回当前管理员的数据
     * @var string
     */
    protected $dataLimit = null;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * select自定义排序,例如：sort,desc
     */
    protected $orderBy = null;

    /**
     * 查询 
     * @param \support\Request $request
     * @return array
     */
    public function select(Request $request)    
    {
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
        $model = $this->selectMap($where,$order);

        $res = ['items' => [], 'total' => 0]; 
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
        $res = collect($res)->toArray();
        return $res;
    }

    /**
     * 获取详情
     * @param Request $request
     * @throws BusinessException
     */
    public function info(Request $request) 
    {
        $model = $this->model->query();
        $primary_key = $model->getModel()->getKeyName();

        // with条件
        if ($this->withArr) {
            $model->with($this->withArr);
        }

        if (!$primary_key) {
            throw new BusinessException('该表无主键，不支持删除');
        }
        $id   = $request->get($primary_key);
        $info = $model->where($primary_key,$id)->first();
        if ($this->callBack && is_callable($this->callBack)) {
            $info = call_user_func($this->callBack,$info);
        }
        return ['row'=>$info];
    }

    /**
     * 构建查询条件
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return mixed
     */
    public function selectMap(array $where, string $order= '')
    {
        $model = $this->model->query();
        // with条件
        if ($this->withArr) {
            $model->with($this->withArr);
        }
        // 指定select查询字段
        if ($this->selectArr) {
           $model->select($this->selectArr);
        }

        // 搜索筛选关联信息
        if ($this->hasArr) {
            foreach($this->hasArr as $li) {
                if (isset($li['relation_name']) && isset($li['where'])) {
                    $model->whereHas(
                        $li['relation_name'], 
                        function($query) use ($li) {
                            $query->where($li['where']);
                        }
                    );
                }

                if (isset($li['relation_name']) && isset($li['whereIn'])) {
                    $model->whereHas(
                        $li['relation_name'],
                        function($query) use ($li) {
                            $query->whereIn($li['whereIn'][0],$li['whereIn'][1]);
                        }
                    );
                }
            }
        }


        $this->whereArr = array_merge($this->whereArr,$where);
        foreach ($this->whereArr as $value) {
            if (isset($value["opt"])) {
                if (!empty($value["val"])) {
                    is_string($value['val']) && trim($value['val']); 
                    if (strtolower($value["opt"]) == 'like' || strtolower($value["opt"]) == 'not like'){
                        $model->where($value["field"],$value["opt"], "%".$value["val"]."%");
                    }elseif (in_array(strtolower($value["opt"]),['>', '=', '<', '<>','>=','<='])) {
                        $model->where($value["field"], $value["val"]);
                    }elseif (strtolower($value["opt"]) == 'in') {
                        $model->whereIn($value["field"], $value["val"]);
                    }elseif (strtolower($value["opt"]) == 'not in') {
                        $valArr = $value['val'];
                        if (is_string($valArr)) {
                            $valArr = explode(",", trim($valArr));
                        }
                        $model = $model->whereNotIn($value['field'], $valArr);
                    }elseif (strtolower($value["opt"]) == 'null') {
                        $model = $model->whereNull($value["field"]);
                    } elseif (strtolower($value["opt"]) == 'not null') {
                        $model = $model->whereNotNull($value["field"]);
                    } elseif(strtolower($value['opt']) == 'range' || strtolower($value['opt']) == 'between') {
                        $valArr = $value['val'];
                        if (is_string($valArr)) {
                            $valArr = explode(',',$valArr);
                        }
                        $valArr = array_filter($valArr);
                        if ($valArr) {
                            $model->whereBetween($value["field"], $valArr);
                        }
                    }
                }
            }else{
                $model->where($value);
            }
        }


        if ($order && strpos($order,",")) {
            $order = explode(",",$order);
            foreach ($order as $key => $val) {
                $rKey = $key + 1;
                if ($rKey % 2 == 0) {
                    $model->orderBy($order[$key - 1], $order[$key]);
                }
            }
        }

        return $model;
    }

    /**
     * 新增数据
     */
    public function insert(Request $request)
    {
        $data = $this->insertInput($request);
        $id   = $this->doInsert($data);
        if ($id) {
            return ["id"=>$id];
        }
        return false;
    }

    /**
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    public function insertInput(Request $request):array
    {
        return $this->inputFilter($request->post());
    }

    /**
     * 执行插入
     * @param array $data
     * @return mixed|null
     */
    protected function doInsert(array $data)
    {
        
        $primary_key = $this->model->getKeyName();
        if ($this->callBack && is_callable($this->callBack)) {
            $data = call_user_func($this->callBack,$data);
        }

        foreach ($data as $key => $val) {
            $this->model->{$key} = $val;
        }
        $this->model->save();
        return $primary_key ? $this->model->$primary_key : null;
    }

    /**
     * 用户输入表单过滤
     * @param array $data
     * @return array
     * @throws BusinessException
     */
    public function inputFilter(array $data=[]):array
    {
        if ($this->params) {
            $data = array_merge($data, $this->params);
        }

        $table = $this->model->getTable();
        $allow_column = Db::select("desc `$table`");
        if (!$allow_column) {
            throw new BusinessException('表不存在', 2);
        }
        $columns = array_column($allow_column, 'Type', 'Field');
        foreach ($data as $col => $item) {
            if (!isset($columns[$col])) {
                unset($data[$col]);
                continue;
            }
            // 非字符串类型传空则为null
            //if ($item === '' && strpos(strtolower($columns[$col]), 'varchar') === false && strpos(strtolower($columns[$col]), 'text') === false) {
            //    $data[$col] = null;
            //}
        }
        if (empty($data['crete_time'])) {
            unset($data['crete_time']);
        }
        if (empty($data['update_time'])) {
            unset($data['update_time']);
        }
        return $data;
    }

  
    /**
     * 
     * 更新数据
     * @param mixed $request
     * @throws \support\exception\BusinessException
     * @return array{row: mixed|bool}
     */
    public function update($request)
    {
        if ($request->method() == "POST") {
            [$id, $data] = $this->updateInput($request);
            // 调用doUpdate方法并检查返回值是否为true
            if (!$this->doUpdate($id, $data)) {
                throw new BusinessException('更新失败');
            }
            return true;
        } else {
            $model = $this->model;
            // with条件
            if ($this->withArr) {
                $model = $model->with($this->withArr);
            }
            $primary_key = $this->model->getKeyName();
            if (!$primary_key) {
                throw new BusinessException('该表无主键，不支持更新');
            }
            $id   = $request->get($primary_key);
            $info = $model->where($primary_key,$id)->first();
            if ($this->callBack && is_callable($this->callBack)) {
                $info = call_user_func($this->callBack, $info);
            }

            return ["row"=>$info];
        }
    }

    /**
     * 更新前置方法
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function updateInput(Request $request): array
    {
        $model = $this->model;
        $primary_key = $model->getKeyName();
        $id   = $request->post($primary_key);
        $data = $this->inputFilter($request->post());
        unset($data[$primary_key]);
        return [$id, $data];
    }

    /**
     * 执行更新
     * @param $id
     * @param $data
     * @return void
     * @throws BusinessException
     */
    protected function doUpdate($id, $data)
    {
        $model = $this->model;
        $record = $model->find($id);
        if (!$record) {
            throw new BusinessException('记录不存在', 2);
        }
        if ($this->callBack && is_callable($this->callBack)) {
            $data = call_user_func($this->callBack, $data);
        }

        foreach ($data as $key => $val) {
            $record->{$key} = $val;
        }
        return $record->save();
    }

    /**
     * @param Request $request
     * @throws BusinessException
     */
    public function delete(Request $request) 
    {
        $ids = $this->deleteInput($request);
        $res = $this->doDelete($ids);
        return $res;
    }

    /**
     * 删除前置方法
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function deleteInput(Request $request): array
    {
        $primary_key = $this->model->getKeyName();
        if (!$primary_key) {
            throw new BusinessException('该表无主键，不支持删除');
        }
        return (array)$request->input($primary_key, []);
    }

    /**
     * 执行删除
     * @param array $ids
     * @return void
     */
    protected function doDelete(array $ids):int
    {
        if (!$ids) {
            throw new BusinessException("ID值不能为空!");
        }
        $primary_key = $this->model->getKeyName();
        return $this->model->whereIn($primary_key, $ids)->delete();
    }

 
    protected function getLengthValue($schema)
    {
        $type = $schema->DATA_TYPE;
        if (in_array($type, ['float', 'decimal', 'double'])) {
            return "{$schema->NUMERIC_PRECISION},{$schema->NUMERIC_SCALE}";
        }
        if ($type === 'enum') {
            return implode(',', array_map(function($item){
                return trim($item, "'");
            }, explode(',', substr($schema->COLUMN_TYPE, 5, -1))));
        }
        if (in_array($type, ['varchar', 'text', 'char'])) {
            return $schema->CHARACTER_MAXIMUM_LENGTH;
        }
        if (in_array($type, ['time', 'datetime', 'timestamp'])) {
            return $schema->CHARACTER_MAXIMUM_LENGTH;
        }
        return '';
    }

    /**
     * @param Request $request
     * @return array|\support\Response
     */
    protected function selectInput(Request $request)
    {
        $order = $request->get('order', 'id,desc');
        $page_size = $request->get('limit', 10);
        $this->pageSize = $page_size;
        $table = $this->model->getTable();
        $allow_column = Db::select("desc `$table`");
        if (!$allow_column) {
            return $this->json(2, '表不存在');
        }

        $where = [];
        // 按照数据限制字段返回数据
        if ($this->dataLimit === 'personal') {
            $where[$this->dataLimitField] = admin_id();
        } elseif ($this->dataLimit === 'auth') {
            $primary_key = $this->model->getKeyName();
            if (!Auth::isSupperAdmin() && $this->dataLimitField != $primary_key) {
//                $where[$this->dataLimitField] = ['in', Auth::getScopeAdminIds(true)];
                $where[] = ['operator'=>'in','field'=>$this->dataLimitField,'val'=> Auth::getScopeAdminIds(true)];
            }
        }

        return [$where, $page_size, $order];
    }
}
