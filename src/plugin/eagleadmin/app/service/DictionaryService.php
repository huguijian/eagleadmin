<?php

namespace plugin\eagleadmin\app\service;

use plugin\eagleadmin\app\model\EmsDict;

class DictionaryService
{
    public function getDicNameArr($dictCode='')
    {
        $res = EmsDict::where('dict_code',$dictCode)->pluck('dict_name','dict_value');
        return $res;
    }
}