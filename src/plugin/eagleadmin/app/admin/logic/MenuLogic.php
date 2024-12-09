<?php

namespace plugin\eagleadmin\app\admin\logic;

use Illuminate\Support\Facades\Request;
use plugin\eagleadmin\utils\Helper;
use plugin\eagleadmin\app\model\EgMenu;

class MenuLogic
{
    public function menu($search)
    {
        $query = EgMenu::query();
        if ($search) {
            $search = array_filter($search);
            $query->where($search);
        }
        if (request()->input('tree', 'false') === 'true')
        {
            $query->field('id, id as value, name as label, parent_id');
        }
        $query->orderBy('sort', 'desc');
        $data = $query->get()
            ->toArray();
        return Helper::makeTree($data);
    }
}
