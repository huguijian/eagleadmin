<?php
namespace plugin\eagleadmin\api;
use plugin\eagleadmin\app\model\EgUser;

class User
{
    use \plugin\eagleadmin\app\Crud;

    public function __construct()
    {
        $this->model = new EgUser();
    }
    /**
     * 获取所有用户列表
     * @param \support\Request $request
     * @return array
     */
    public function getUserList(\support\Request  $request)
    {
        [$where, $pageSize, $order] = $this->selectInput($request);
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
}