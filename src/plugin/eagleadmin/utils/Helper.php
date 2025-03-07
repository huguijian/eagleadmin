<?php

namespace plugin\eagleadmin\utils;
/**
 * 帮助类
 */
class Helper
{
    /**
     * 数据树形化
     * @param array $data 数据
     * @param string $childrenname 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     * @return array
     */
    public static function makeTree(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'parent_id')
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        $tree = []; //格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }
        return $tree;
    }

    /**
     * 生成Arco菜单
     * @param array $data 数据
     * @param string $childrenname 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     * @return array
     */
    public static function makeArcoMenus(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'parent_id')
    {
        $list = [];
        foreach ($data as $value) {
            if ($value['type'] === 'M'){
                $path = '/'.$value['route'];
                $layout = isset($value['is_layout']) ? $value['is_layout'] : 1;
                $temp = [
                    $keyName => $value[$keyName],
                    $pidName => $value[$pidName],
                    'name' => str_replace('/', '_', $value['route']),
                    'path' => $path,
                    'component' => $value['component'],
                    //'redirect' => $value['redirect'],
                    'meta' => [
                        'title' => $value['name'],
                        'type' => $value['type'],
                        'hidden' => $value['is_hidden'] === 1,
                        'layout' => $layout === 1,
                        'hiddenBreadcrumb' => false,
                        'icon' => $value['icon'],
                        'href' => $value['redirect'] ?? '',
                    ],
                ];
                $list[$value[$keyName]] = $temp;
            }
			if ($value['type'] === 'I' || $value['type'] === 'L'){
                $temp = [
                    $keyName => $value[$keyName],
                    $pidName => $value[$pidName],
                    'name' => $value['code'],
                    'path' => $value['route'],
                    'meta' => [
                        'title' => $value['name'],
                        'type' => $value['type'],
                        'hidden' => $value['is_hidden'] === 1,
                        'hiddenBreadcrumb' => false,
                        'icon' => $value['icon'],
                    ],
                ];
                $list[$value[$keyName]] = $temp;
            }
        }
        $tree = []; //格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }
        return $tree;
    }

    /**
     * 获取所有子级id
     * @param array $data
     * @param mixed $pid
     * @return array
     */
    public static function getChildrenIds(array $data,$pid): array
    {
        foreach ($data as $item) {
            if ($pid==$item['parent_id']) {
                $childrenIds[] = $item['id'];
                $childrenIds = array_merge($childrenIds,self::getChildrenIds($data,$item['id']));
            }
        }
        return $childrenIds ?? [];
    }
}
