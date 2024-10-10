<?php

namespace plugin\eagleadmin\app\controller;

use support\Request;

class IndexController
{
    public function index()
    {
        return json(['a'=>29340290]);
        return view('index/index', ['name' => 'ems']);
    }

}
