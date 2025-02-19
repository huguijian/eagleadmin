<?php

namespace plugin\eagleadmin\app\admin\logic;

use Illuminate\Support\Facades\Request;
use plugin\eagleadmin\utils\Helper;
use plugin\eagleadmin\app\model\EgMenu;
use support\Db;

class MenuLogic
{
    public function menu($search,$onlyTrashed=false)
    {
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
}
