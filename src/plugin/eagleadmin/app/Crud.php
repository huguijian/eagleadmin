<?php

namespace plugin\eagleadmin\app;

use plugin\eagleadmin\app\common\Auth;
use support\Db;
use support\exception\BusinessException;
use support\Model;
use support\Request;
use support\Response;

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
    protected $whereArr = null;

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
     *
     * @param Request $request
     * @return \support\Response
     */
    public function select(Request $request):Response
    {
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
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
     * 获取详情
     * @param Request $request
     * @param $model
     * @return Response
     * @throws BusinessException
     */
    public function info(Request $request,$model=null): Response
    {
        if (is_null($model)) {
            $model = $this->model->query();
        }else{
            $model = $model->query();
        }

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
        return $this->success(["row"=>$info]);
    }

    /**
     * 构建查询条件
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return mixed
     */
    public function selectMap(array $where, string $order= '',object $model=null)
    {
        if (is_null($model)) {
            $model = $this->model->query();
        }else{
            $model = $model->query();
        }
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

        foreach ($where as $column=>$value) {
            if (isset($value["operator"])) {
                if (strtolower($value["operator"]) == 'like' || strtolower($value["operator"]) == 'not like'){
                    $model->where($value["field"],$value["operator"], "%".$value["val"]."%");
                }elseif (in_array(strtolower($value["operator"]),['>', '=', '<', '<>','>=','<='])) {
                    $model->where($value["field"], $value["val"]);
                }elseif (strtolower($value["operator"]) == 'in') {
                    $model->whereIn($value["field"], $value["val"]);
                }elseif (strtolower($value["operator"]) == 'not in') {
                    $valArr = $value['val'];
                    if (is_string($valArr)) {
                        $valArr = explode(",", trim($valArr));
                    }
                    $model = $model->whereNotIn($value['field'], $valArr);
                }elseif (strtolower($value["operator"]) == 'null') {
                    $model = $model->whereNull($value["field"]);
                } elseif (strtolower($value["operator"]) == 'not null') {
                    $model = $model->whereNotNull($value["field"]);
                } elseif(strtolower($value['operator']) == 'range' || strtolower($value['operator']) == 'between') {
                    $valArr = $value['val'];
                    if (is_string($valArr)) {
                        $valArr = explode(',',$valArr);
                    }
                    $model->whereBetween($value["field"], $valArr);
                }
            }else{
                $model->where($value);
            }
        }
        // 附加查询条件
        if ($this->whereArr) {
            //$model = $model->where(DB::raw("num"), "<=", DB::raw("alarm_num"));
            $model->where($this->whereArr);
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
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request):Response
    {
        $data = $this->insertInput($request);
        $id   = $this->doInsert($data);
        if ($id) {
            return $this->success(["id"=>$id]);
        }
        return $this->error('保存失败!');
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
    protected function doInsert(array $data,object $model=null)
    {
        if (is_null($model)) {
            $model = $this->model;
        }
        $primary_key = $model->getKeyName();
        $model_class = get_class($model);
        $model = new $model_class;

        if ($this->callBack && is_callable($this->callBack)) {
            $data = call_user_func($this->callBack,$data);
        }

        foreach ($data as $key => $val) {
            $model->{$key} = $val;
        }
        $model->save();
        return $primary_key ? $model->$primary_key : null;
    }

    /**
     * 用户输入表单过滤
     * @param array $data
     * @return array
     * @throws BusinessException
     */
    public function inputFilter(array $data=[],$model=null):array
    {
        if ($this->params) {
            $data = array_merge($data, $this->params);
        }

        if (is_null($model)) {
            $table = $this->model->getTable();
        }else{
            $table = $model->getTable();
        }
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
     * 更新数据
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ($request->method() == "POST") {
            [$id, $data] = $this->updateInput($request);

            $res = $this->doUpdate($id, $data);
            if ($res) {
                return $this->success([]);
            }
            return $this->error('更新失败!');
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

            return $this->success(["row" => $info]);
        }
    }

    /**
     * 更新前置方法
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function updateInput(Request $request,object $model=null): array
    {
        if (is_null($model)) {
            $model = $this->model;
        }
        $primary_key = $model->getKeyName();
        $id   = $request->post($primary_key);
        $data = $this->inputFilter($request->post(),$model);
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
    protected function doUpdate($id, $data, $model=null)
    {
        if (is_null($model)) {
            $model = $this->model;
        }
        $model = $model->find($id);
        if (!$model) {
            throw new BusinessException('记录不存在', 2);
        }

        if ($this->callBack && is_callable($this->callBack)) {
            $data = call_user_func($this->callBack, $data);
        }

        foreach ($data as $key => $val) {
            $model->{$key} = $val;
        }
        return $model->save();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $res = $this->doDelete($ids);
        if ($res) {
            return $this->success([]);
        } else {
            return $this->error('删除失败!');
        }
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
    protected function doDelete(array $ids)
    {
        if (!$ids) {
            throw new BusinessException("ID值不能为空!");
        }
        $primary_key = $this->model->getKeyName();
        return $this->model->whereIn($primary_key, $ids)->delete();
    }

    /**
     * 摘要
     * @param Request $request
     * @return \support\Response
     * @throws \Support\Exception\BusinessException
     */
    public function schema(Request $request,object $model=null)
    {
        if (is_null($model)) {
            $table = $this->model->getTable();
        }else{
            $table = $model->getTable();
        }

        Util::checkTableName($table);
        $schema = Option::where('name', "table_form_schema_$table")->value('value');
        $form_schema_map = $schema ? json_decode($schema, true) : [];

        $data = $this->getSchema($table);
        foreach ($data['forms'] as $field => $item) {
            if (isset($form_schema_map[$field])) {
                $data['forms'][$field] = $form_schema_map[$field];
            }
        }

        return $this->json(0, 'ok', [
            'table' => $data['table'],
            'columns' => array_values($data['columns']),
            'forms' => array_values($data['forms']),
            'keys' => array_values($data['keys']),
        ]);
    }

    /**
     * 按表获取摘要
     *
     * @param $table
     * @param $section
     * @return array|mixed
     */
    protected function getSchema($table, $section = null)
    {
        $database = config('database.connections')['plugin.admin.mysql']['database'];
        $schema_raw = $section !== 'table' ? Util::db()->select("select * from information_schema.COLUMNS where TABLE_SCHEMA = '$database' and table_name = '$table'") : [];
        $forms = [];
        $columns = [];
        foreach ($schema_raw as $item) {
            $field = $item->COLUMN_NAME;
            $columns[$field] = [
                'field' => $field,
                'type' => Util::typeToMethod($item->DATA_TYPE, (bool)strpos($item->COLUMN_TYPE, 'unsigned')),
                'comment' => $item->COLUMN_COMMENT,
                'default' => $item->COLUMN_DEFAULT,
                'length' => $this->getLengthValue($item),
                'nullable' => $item->IS_NULLABLE !== 'NO',
                'primary_key' => $item->COLUMN_KEY === 'PRI',
                'auto_increment' => strpos($item->EXTRA, 'auto_increment') !== false
            ];

            $forms[$field] = [
                'field' => $field,
                'comment' => $item->COLUMN_COMMENT,
                'control' => Util::typeToControl($item->DATA_TYPE),
                'form_show' => $item->COLUMN_KEY !== 'PRI',
                'list_show' => true,
                'enable_sort' => false,
                'readonly' => $item->COLUMN_KEY === 'PRI',
                'searchable' => false,
                'search_type' => 'normal',
                'control_args' => '',
            ];
        }
        $table_schema = $section == 'table' || !$section ? Util::db()->select("SELECT TABLE_COMMENT FROM  information_schema.`TABLES` WHERE  TABLE_SCHEMA='$database' and TABLE_NAME='$table'") : [];
        $indexes = $section == 'keys' || !$section ? Util::db()->select("SHOW INDEX FROM `$table`") : [];
        $keys = [];
        foreach ($indexes as $index) {
            $key_name = $index->Key_name;
            if ($key_name == 'PRIMARY') {
                continue;
            }
            if (!isset($keys[$key_name])) {
                $keys[$key_name] = [
                    'name' => $key_name,
                    'columns' => [],
                    'type' => $index->Non_unique == 0 ? 'unique' : 'normal'
                ];
            }
            $keys[$key_name]['columns'][] = $index->Column_name;
        }

        $data = [
            'table' => ['name' => $table, 'comment' => $table_schema[0]->TABLE_COMMENT ?? ''],
            'columns' => $columns,
            'forms' => $forms,
            'keys' => array_reverse($keys, true)
        ];
        return $section ? $data[$section] : $data;
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
    protected function selectInput(Request $request, $model=null): Response|array
    {
        $order = $request->get('order', 'id,desc');
        $page_size = $request->get('limit', 10);
        $this->pageSize = $page_size;
        $where = $request->get("search") ?? [];
        if (is_null($model)) {
            $table = $this->model->getTable();
        }else{
            $table = $model->getTable();
        }


        $allow_column = Db::select("desc `$table`");
        if (!$allow_column) {
            return $this->json(2, '表不存在');
        }
        $allow_column = array_column($allow_column, 'Field', 'Field');

        foreach ($where as $key => $value) {
            if ($value["val"] === '' || !isset($allow_column[$value["field"]]) ||
                (is_array($value) && ($value["field"] == 'undefined' || $value["val"] == 'undefined'))) {
                unset($where[$key]);
            }
        }

        // 按照数据限制字段返回数据
        if ($this->dataLimit === 'personal') {
            $where[$this->dataLimitField] = admin_id();
        } elseif ($this->dataLimit === 'auth') {
            $primary_key = $this->model->getKeyName();
            if (!Auth::isSupperAdmin() && (!isset($where[$primary_key]) || $this->dataLimitField != $primary_key)) {
//                $where[$this->dataLimitField] = ['in', Auth::getScopeAdminIds(true)];
                $where[] = ['operator'=>'in','field'=>$this->dataLimitField,'val'=> Auth::getScopeAdminIds(true)];
            }
        }

        return [$where, $page_size, $order];
    }

    protected function json(int $code, string $msg = 'ok', array $data = [])
    {
        return json(['code' => $code, 'result' => $data, 'message' => $msg, 'type' => $code ? 'error' : 'success']);
    }
}
