<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const ZERO_DATETIME = '1970-01-01 00:00:00';

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
    public static function getOneRowById($id, $fields = null, $toArray = true)
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
    public static function getOneRowByWhere(array $where, $options = [], $toArray = true)
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
        $query = self::getQueryByWhere($where, $options);
        isset($options['order']) && $query->orderByRaw($options['order']);
        isset($options['group']) && $query->groupBy($options['group']);
        isset($options['fields']) && $query->select($options['fields'] ?: ['*']);
        if (isset($options['page']) && isset($options['pageSize'])) { // 支持分页
            $query->forPage($options['page'], $options['pageSize']);
        }

        return $query;
    }

    /**
     * 根据条件生成query
     *
     * @param       $where
     * @param array $options
     * @return Builder
     * @throws \ErrorException
     */
    private static function getQueryByWhere($where, $options = [])
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
    public static function getListByWhere(array $where, $options = [], $toArray = true)
    {
        $query = self::getWhereQuery($where, $options);

        /* @var $collection \Illuminate\Support\Collection */
        $collection = $query->get();

        if (isset($options['index'])) {
            $result = [];
            $fieldKey = $options['index'];
            foreach ($collection as $item) {
                $item = $item->toArray();
                $result[self::getIndexKey($fieldKey, $item)] = $item;
            }

            return $result;
        }

        return $toArray && $collection ? $collection->toArray() : $collection;
    }

    /**
     * 获取查询数量
     *
     * @param array $where
     * @param array $options
     * @return int
     * @throws \ErrorException
     */
    public static function getCountByWhere(array $where, $options = [])
    {
        $query = self::getWhereQuery($where, $options);

        return $query->count();
    }

    /**
     * 根据条件更新数据
     *
     * @param array $where
     * @param array $updateData
     * @return int
     * @throws \ErrorException
     */
    public static function updateByWhere(array $where, array $updateData)
    {
        $query = self::getQueryByWhere($where);

        return $query->update($updateData);
    }

    /**
     * 根据条件删除
     *
     * @param array $where
     * @return int
     * @throws \ErrorException
     */
    public static function deleteByWhere(array $where)
    {
        $query = self::getQueryByWhere($where);

        return $query->delete();
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
        $query = self::getWhereQuery($where, $options);

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
    public static function getOneRowByWhereFromTable($table, $where, $options = [], $toArray = true)
    {
        $options['table'] = $table;
        $query = self::getWhereQuery($where, $options);
        $item = $query->first($options['fields'] ?? ['*'] ?: ['*']);

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
    public static function getListByWhereFromTable($table, array $where, $options = [], $toArray = true)
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
     * @return bool
     */
    public static function multiInsertFromTable($table, $list)
    {
        return \DB::table($table)->insert($list);
    }

    /**
     * 根据条件查询数量
     *
     * @param       $table
     * @param array $where
     * @return int
     */
    public static function getCountByWhereFromTable($table, array $where)
    {
        $options = ['table' => $table];
        $query = self::getWhereQuery($where, $options);
        return $query->count();
    }


    /**
     * 根据条件更新
     *
     * @param string $table
     * @param array  $where
     * @param array  $data
     *
     * @return int
     */
    public static function updateByWhereFromTable($table, array $where, $data)
    {
        $options = ['table' => $table];

        $query = self::getQueryByWhere($where, $options);

        return $query->update($data);
    }

    /**
     * 根据条件删除
     *
     * @param string $table
     * @param array  $where
     * @return int
     */
    public static function deleteByWhereFromTable($table, array $where)
    {
        $options = ['table' => $table];
        $query = self::getQueryByWhere($where, $options);

        return $query->delete();
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
    public static function getOneRowByRaw($sql, $binding = [], $toArray = true)
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
                $item = (array)$item;
                $result[$item[$keyField]] = $item[$valueField];
            }
        } else {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item = (array)$item;
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
                $item = (array)$item;
                $result[self::getIndexKey($index, $item)] = $item;
            }
        } else {
            foreach (\DB::cursor($sql, $binding) as $item) {
                $item = (array)$item;
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
    private static function getIndexKey($index, $data)
    {
        if (is_string($index)) $index = [$index];
        $temp = [];
        foreach ($index as $item) {
            $temp[] = $data[$item];
        }

        return implode('_', $temp);
    }

    /**
     * 按唯一键数组 获取数据 类似 主键的 in
     *
     * @param       $table      string 表名
     * @param       $list       array 同一结构数据集
     * @param array $uniqueKey  数据集中可构成唯一键的 字段数组
     * @param array $otherField 需要返回的其他字段
     *
     * @return array|bool
     */
    public static function multiUniqueKeysSelectFromTable($table, $list, array $uniqueKey, $otherField = [])
    {
        if (!$table || !$list) return false;
        $where = [];
        $binding = [];
        foreach ($list as $item) {
            $tempWhere = [];
            foreach ($uniqueKey as $key) {
                $tempWhere[] = sprintf('%s = ?', $key);
                $binding[] = $item[$key];
            }
            $where[] = '(' . implode(' AND ', $tempWhere) . ')';
        }
        $sql = 'select ' . implode(',', array_merge($uniqueKey, $otherField)) . ' from ' . $table . ' where ' . implode(' OR ', $where);

        return self::getListByRaw($sql, $binding, $uniqueKey);
    }

    /**
     * 批量更新或者插入：会造成自增主键不连续 空洞过大，慎用！！！
     * 比较适用于自增的表
     *
     * @param       $table        string 表名
     * @param       $list         array  插入更新的数据, 每个数据key必须一样
     * @param array $updateFields 主键|唯一键重复时需要更新的进行更新的字段
     *
     * @return bool|int
     */
    public static function multiInsertOrUpdateFrom($table, $list, $updateFields = [])
    {
        if (!$table || !$list) return false;

        $fields = array_keys(reset($list));
        $values = [];
        $binding = [];

        $qArr = array_fill(0, count($fields), '?');
        $qStr = '(' . implode(',', $qArr) . ')';
        foreach ($list as $index => $item) {
            $binding = array_merge($binding, array_values($item));
            $values[] = $qStr;
        }
        $sql = sprintf('insert into %s (%s) values %s', $table, implode(',', $fields), implode(',', $values));
        $sql .= ' ON DUPLICATE KEY UPDATE ';
        foreach ($updateFields as $field) {
            $sql .= sprintf('%s=values(%s),', $field, $field);
        }
        $sql = substr($sql, 0, -1);

        return \DB::statement($sql, $binding);
    }


    /**
     * 生成条件
     *
     * @param $data    array 一条记录数据
     * @param $keys    array 记录中进行组合条件的字段
     * @param $binding array 绑定数据
     *
     * @return string 条件语句
     */
    private static function createCondition($data, $keys, &$binding)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = sprintf('%s=?', $key);
            $binding[] = $data[$key];
        }

        return implode(' AND ', $result);
    }

    /**
     * 批量更新
     *
     * @param              $table        string 表名
     * @param              $list         ['keyvalue'=>[data]] array 主键=>更新数据关联数组
     * @param array|string $uniqueKey    组合成的更新条件 list 元素中
     * @param array        $updateFields 更新的字段 list元素中
     *
     * @return bool|int
     */
    public static function multiUpdateByCaseWhen($table, $list, $uniqueKey, $updateFields = [])
    {
        if (is_string($uniqueKey)) $uniqueKey = [$uniqueKey];
        $whenThen = [];
        $where = [];
        if (!$updateFields) {
            $updateFields = array_diff(array_keys(reset($list)), $uniqueKey);
        }
        $whereBinding = [];
        $caseFieldBinding = [];
        foreach ($list as $key => $data) {
            foreach ($updateFields as $field) {
                if (!isset($caseFieldBinding[$field])) $caseFieldBinding[$field] = [];
                if (isset($whenThen[$field])) {
                    $whenThen[$field] .= sprintf(' when %s then ?', self::createCondition($data, $uniqueKey, $caseFieldBinding[$field]));
                } else {
                    $whenThen[$field] = sprintf('%s = case when %s then ?', $field, self::createCondition($data, $uniqueKey, $caseFieldBinding[$field]));
                }
                $caseFieldBinding[$field][] = $data[$field];
            }
            $where[] = '(' . self::createCondition($data, $uniqueKey, $whereBinding) . ')';
        }

        $mergeBinding = [];
        foreach ($caseFieldBinding as $key => $bind) {
            $mergeBinding = array_merge($mergeBinding, $bind);
        }

        $mergeBinding = array_merge($mergeBinding, $whereBinding);

        $sql = 'update ' . $table . ' set ';
        foreach ($whenThen as $item) {
            $sql .= $item . ' end,';
        }
        $sql = substr($sql, 0, -1);
        $sql .= ' where ' . implode(' OR ', $where);

        return \DB::statement($sql, $mergeBinding);
    }

    /**
     * 获取日期时间
     *
     * @return mixed
     */
    public static function getDatetime()
    {
        return date('Y-m-d H:i:s');
    }
}
