<?php

namespace plugin\eagleadmin\app\admin\logic\dictionary;

use plugin\eagleadmin\app\model\EmsDict;

class DictionaryLogic
{
    /**
     * 根据字典名称获取字典值
     * @param $dictName
     * @param $enum
     * @return string
     */
    public static function getDicVal($dictName='',$enum='')
    {
        $res = EmsDict::where('dict_code',$enum)->pluck('dict_value','dict_name');
        return $res[$dictName]??"";
    }
}