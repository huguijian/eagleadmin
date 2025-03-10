<?php
namespace plugin\eagleadmin\app\logic\auth;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\utils\Helper;
use plugin\eagleadmin\app\model\EgMenu;
use support\Db;

class MenuLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgMenu();
    }
    
    /**
     * 获取菜单数据
     * @param mixed $request
     * @param mixed $onlyTrashed
     * @return array
     */
    public function menu($request,$onlyTrashed=false)
    {
        
        $search = $this->inputFilter($request->all());
        $query = EgMenu::query();
        if ($search) {
            $search = array_filter($search);
            $query->where($search);
        }
        if (request()->input('tree', 'false') === 'true')
        {
            $query->select('id', Db::raw('id as value'), Db::raw('name as label'), 'parent_id');
        }
        $query->orderBy('sort', 'desc');
        if ($onlyTrashed){
            $query->onlyTrashed();
        }
        $data = $query->get()
            ->toArray();
        return Helper::makeTree($data);
    }
    
    /**
     * 添加菜单
     * @param mixed $request
     * @return array{id: mixed|bool}
     */
    public function insert($request)
    {
        $data = $this->insertInput($request);
        $data['parent_id'] = empty($data['parent_id']) ? 0 : $data['parent_id'];
        $id   = $this->doInsert($data);
        if ($id) {
            return ["id"=>$id];
        }
        return false;
    }
}
