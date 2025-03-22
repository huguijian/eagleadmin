<?php
namespace plugin\eagleadmin\app\service;
use plugin\eagleadmin\app\model\EgDict;

class DictService
{
    private static $instance = null;
    private $dictCache = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDictByType($type)
    {
        if (!isset($this->dictCache[$type])) {
            $dictList = EgDict::where('dict_code', $type)->get()->toArray();
            $this->dictCache[$type] = $dictList;
        }
        return $this->dictCache[$type];
    }

    /**
     * 获取字典标签
     * @param mixed $dictType
     * @param mixed $dictValue
     * @param mixed $default
     */
    public function getDictLable($dictType, $dictValue,$default = '')
    {
        $dictList = $this->getDictByType($dictType);
        foreach ($dictList as $dict) {
            if ($dict['dict_value'] == $dictValue) {
                return $dict['dict_name'];
            }
        }
        return $default;
    }

    /**
     * 获取字典映射
     * @param mixed $dictType
     * @return array
     */
    public function getDictMap($dictType)
    {
        $dictList = $this->getDictByType($dictType);
        $dictMap = [];
        foreach ($dictList as $dict) {
            $dictMap[$dict['dict_value']] = $dict['dict_name'];
        }
        return $dictMap;
    }

    /**
     * 清除缓存
     * @param mixed $dictType
     * @return void
     */
    public function clearCache($dictType = null)
    {
        if ($dictType === null) {
            $this->dictCache = [];
        } else {
            unset($this->dictCache[$dictType]);
        }
    }
}