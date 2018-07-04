<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 不自动维护两个字段 created_at 和 updated_at
     *
     * @var bool
     */
    public $timestamps = false;


    ####################################################################################################################
    #                                            Eloquent 模型对象映射查询                                               #
    ####################################################################################################################
    /**
     * 根据主键获取单条记录
     *
     * @param int|string $id      主键数据
     * @param array      $fields  主键数据
     * @param bool       $toArray 是否返回数组结构
     *
     * @return Model|null|array
     */
    public static function getOneRowById($id, $fields = null, $toArray = false)
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = static::find($id, $fields ?: ['*']);

        return $toArray ? ($model ? $model->toArray() : null) : $model;
    }

    /**
     * 根据查询条件获取单条记录
     *
     * @param array $where
     * @param array $options
     * @param bool  $toArray 是否返回数组结构
     *
     * @return Model|null|array
     * @throws
     */
    public static function getOneRowByWhere(array $where, $options = [], $toArray = false)
    {
        $query = self::getWhereQuery($where, $options);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $query->first($options['fields'] ?? ['*'] ?: ['*']);

        return $toArray ? ($model ? $model->toArray() : null) : $model;
    }

    /**
     * 根据主键获取单个字段值
     *
     * @param string $id    主键数据
     * @param string $field 返回的字段名
     *
     * @return mixed|null
     */
    public static function getOneFieldById($id, $field)
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = static::find($id, [$field]);

        return $model ? $model[$field] : null;
    }

    /**
     * 根据查询条件获取单个字段
     *
     * @param array  $where 查询条件
     * @param string $field 字段
     *
     * @return mixed
     */
    public static function getOneFieldByWhere(array $where, $field)
    {
        return static::where($where)->value($field);
    }

    /**
     * 获取一列数据
     *
     * @param array  $where      查询条件
     * @param string $valueField 返回的值字段
     * @param array  $options    排序
     *
     * @return array
     * @throws
     */
    public static function getOneColumnByWhere($where, $valueField, $options = [])
    {
        $query = self::getWhereQuery($where, $options);

        $keyField = $options['index'] ?? null;
        /* @var $collection \Illuminate\Support\Collection */
        $collection = $query->pluck($valueField, $keyField);

        return $collection->toArray();
    }

    /**
     * @param array $where
     * @param array $options
     *
     * @return Builder
     * @throws \ErrorException
     */
    private static function getWhereQuery($where, $options = [])
    {
        $whereIn = null;
        if (isset($where['in'])) {
            $whereIn = $where['in'];
            unset($where['in']);
        }
        /* @var $query Builder */
        $query = isset($options['table']) ? \DB::table($options['table'])->where($where) : static::where($where);

        if ($whereIn) {
            if (isset($whereIn[0]) && count($whereIn) == 2 && is_string($whereIn[0])) {
                list($field, $values) = $whereIn;
                if (!$field || !is_array($values))
                    throw new \ErrorException('in field not found!');
                $query->whereIn($field, $values);
            } else {
                foreach ($whereIn as $field => $values) {
                    if (!is_string($field) || !is_array($values))
                        throw new \ErrorException('in field not found!');
                    $query->whereIn($field, $values);
                }
            }
        }
        isset($options['order']) && $query->orderByRaw($options['order']);
        isset($options['group']) && $query->groupBy($options['group']);
        isset($options['fields']) && $query->select($options['fields'] ?: ['*']);
        if (isset($options['page']) && isset($options['page_num'])) { // 支持分页
            $query->forPage($options['page'], $options['page_num']);
        }

        return $query;
    }

    /**
     * 根据条件获取列表数据
     *
     * @param array      $where   查询条件
     * @param bool|false $toArray 是否返回数组
     * @param array      $options 支持 index fields order group
     *
     * @return array|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public static function getListByWhere(array $where, $options = [], $toArray = false)
    {
        $query = self::getWhereQuery($where, $options);

        /* @var $collection \Illuminate\Support\Collection */
        $collection = $query->get();

        if (isset($options['index'])) {
            $result   = [];
            $fieldKey = $options['index'];
            foreach ($collection as $item) {
                $result[self::getIndexKey($fieldKey, $item)] = $item;
            }
            $collection = $result;
        }

        return $toArray ? $collection->toArray() : $collection;
    }

    ####################################################################################################################
    #                                            DB查询构造器查询                                                        #
    ####################################################################################################################

    /**
     * DB 返回单个字段数据
     *
     * @param string $table
     * @param array  $where
     * @param string $field
     *
     * @return mixed
     *
     * @throws
     */
    public static function getOneFieldByWhereFromTable($table, $where, $field)
    {
        $options['table'] = $table;
        $query            = self::getWhereQuery($where, $options);

        return $query->value($field);
    }

    /**
     * DB 查询单条语句
     *
     * @param string $table 表名
     * @param array  $where 查询条件
     * @param array  $options
     * @param bool   $toArray
     *
     * @return mixed|static
     * @throws
     */
    public static function getOneRowByWhereFromTable($table, $where, $options = [], $toArray = false)
    {
        $options['table'] = $table;
        $query            = self::getWhereQuery($where, $options);
        $item             = $query->first($options['fields'] ?? ['*'] ?: ['*']);

        return $toArray && $item ? (array)$item : $item;
    }

    /**
     * DB 查询单列数据
     *
     * @param string $table      string  表名
     * @param array  $where      array  查询条件
     * @param string $valueField string 返回的值字段
     * @param array  $options    返回的索引字段
     *
     * @return array
     * @throws \Exception
     */
    public static function getOneColumnByWhereFromTable($table, $where, $valueField, $options = [])
    {
        $options['table'] = $table;

        return self::getOneColumnByWhere($where, $valueField, $options);
    }

    /**
     * DB 查询列表数据
     *
     * @param string $table
     * @param array  $where
     * @param array  $options
     * @param bool   $toArray
     *
     * @return array|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public static function getListByWhereFromTable($table, array $where, $options = [], $toArray = false)
    {
        $options['table'] = $table;

        return self::getListByWhere($where, $options, $toArray);
    }

    /**
     * 批量插入
     *
     * @param string $table
     * @param array  $list
     *
     * @return mixed
     */
    public static function multiInsertFromTable($table, $list)
    {
        return \DB::table($table)->insert($list);
    }

    /**
     * 根据条件更新
     *
     * @param string $table
     * @param array  $where
     * @param array  $data
     */
    public static function updateByWhereFromTable($table, $where, $data)
    {
        return \DB::table($table)->where($where)->update($data);
    }

    ####################################################################################################################
    #                                            原生sql语句查询                                                         #
    ####################################################################################################################

    /**
     * 原生语句查询  单条记录
     *
     * @param string $sql
     * @param array  $binding
     * @param bool   $toArray
     *
     * @return array
     */
    public static function getOneRowByRaw($sql, $binding = [], $toArray = false)
    {
        $data = \DB::selectOne($sql, $binding);

        return $toArray && $data ? (array)$data : $data;
    }

    /**
     * 原生语句查询  单个字段
     *
     * @param string $sql
     * @param array  $binding
     *
     * @return mixed|string
     */
    public static function getOneFieldByRaw($sql, $binding = [])
    {
        $data = self::getOneRowByRaw($sql, $binding, true);

        return is_array($data) ? reset($data) : '';
    }

    /**
     * 原生语句查询 获取单列字段
     *
     * @param string $sql
     * @param array  $binding
     * @param string $valueField
     * @param null   $keyField
     *
     * @return array
     */
    public static function getOneColumnByRaw($sql, $binding = [], $valueField, $keyField = null)
    {
        $result = [];
        if ($keyField) {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item                     = (array)$item;
                $result[$item[$keyField]] = $item[$valueField];
            }
        } else {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item     = (array)$item;
                $result[] = $item[$valueField];
            }
        }

        return $result;
    }

    /**
     * 原生语句查询  获取列表数据
     *
     * @param string            $sql
     * @param array             $binding
     * @param string|array|null $index
     *
     * @return array
     */
    public static function getListByRaw($sql, $binding = [], $index = null)
    {
        $result = [];
        if (!is_null($index)) {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item                                     = (array)$item;
                $result[self::getIndexKey($index, $item)] = $item;
            }
        } else {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item     = (array)$item;
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 生成 索引key
     *
     * @param string|array $index
     * @param array        $data
     *
     * @return string
     */
    private static function getIndexKey($index, array $data)
    {
        if (is_string($index)) $index = [$index];
        $temp = [];
        foreach ($index as $item) {
            $temp[] = $data[$item];
        }

        return implode('_', $temp);
    }
}
