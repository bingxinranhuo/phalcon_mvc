<?php
namespace Common\Models;

/**
 * Description of BaseModel
 * @date 2018-4-24 11:35:06
 */
class BaseModel extends \Phalcon\Mvc\Model
{
    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 添加单个
     * @date 2018-04-24 10:37
     * @return int 数据主键ID
     */
    public function addOne($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        foreach ($data as $column => $value) {
            $this->$column = $value;
        }

        if ($this->create()) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @return int | bool 返回最后ID，执行失败返回false。
     */
    public function addMany($dataList)
    {
        if (empty($dataList)) {
            return false;
        }

        //拼接SQL
        $columns = array_keys($dataList[0]);
        $sql = "INSERT INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');

        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * 添加单条数据，当重复时忽略。
     * INSERT IGNORE INSERT (COLUMN1, COLUMN2, COLUMN3, ...) VALUES (1, 2, 3, ...)
     * @date 2018-04-24
     * @param array $data 要添加的数据
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertOneIgnoreDuplicate($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        //拼接SQL
        $columns = array_keys($data);
        $sql = 'INSERT IGNORE INTO ' . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES (';
        foreach ($columns as $col) {
            $sql .= ':' . $col . ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ')';

        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql, $data);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据，重复时忽略
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertManyIgnoreDuplicate($dataList)
    {
        if (empty($dataList)) {
            return false;
        }

        //拼接sql
        $columns = array_keys($dataList[0]);
        $sql = "INSERT IGNORE INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }


    /**
     * 批量添加数据，重复时更新
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @param array $upkey 更新字段
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertManyDuplicate($dataList, $upkey)
    {
        if (empty($dataList)) {
            return false;
        }

        //拼接sql
        $upstr = '';
        foreach ($upkey as $key) {
            $upstr .= $key . " =  " . $key . ",";
        }
        $upstr = substr($upstr, 0, -1);
        $columns = array_keys($dataList[0]);
        $sql = "INSERT  INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');

        $sql .= " ON DUPLICATE KEY UPDATE " . $upstr;
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 单条添加数据，重复时更新
     * @date 2018-04-24
     * @param array $data 数据列表
     * @param array $upkey 更新字段
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertOneDuplicate($data, $upkey)
    {
        if (empty($data)) {
            return false;
        }

        //拼接sql
        $upstr = '';
        foreach ($upkey as $key) {
            $upstr .= $key . "=VALUES (" . $key . "),";
        }
        $upstr = substr($upstr, 0, -1);

        $columns = array_keys($data);
        $sql = "INSERT INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES (';
        foreach ($columns as $col) {
            $sql .= ':' . $col . ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ')';

        $sql .= " ON DUPLICATE KEY UPDATE " . $upstr;
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql, $data);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据主键删除数据
     * @param $pk
     * @return bool
     */
    public function deleteByPrimaryKey($pk)
    {
        $pk = intval($pk);
        if (0 >= $pk) {
            return false;
        }

        $model = $this->getByPrimaryKey($pk);
        if ($model) {
            return $model->delete();
        } else {
            return false;
        }
    }

    /**根据条件更新字段
     *
     */

    /**
     * 根据主键ID更新数据
     * @date 2018-04-24
     * @param int $pk 主键ID
     * @param array $newData 新数据
     * @return bool
     */
    public function updateByPrimaryKey($pk, $newData)
    {
        $pk = intval($pk);
        if (0 >= $pk || empty($newData)) {
            return false;
        }
        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '`=' . "'{$value}',";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE {$this->primaryKey}={$pk}";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据条件修改数据
     * @param $newData
     * @param $where
     * @return bool
     */

    public function updateByWhere($newData,$where)
    {
        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '`=' . "'{$value}',";
        }
        $sql = rtrim($sql, ',')." where ";
        foreach ($where as $column => $value){
            $sql .= '`' . $column . '`=' . "'{$value}' and ";
        }
        $sql = rtrim($sql, ' and ');
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据主键ID更新数据
     * @param $conditions
     * @param $newData
     * @return bool
     */
    public function updateMultiRecords($conditions, $newData)
    {
        if (!$conditions) {
            return false;
        }

        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '` =' . "'{$value}',";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE $conditions";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据条件删除  慎用 硬删除
     * @param $conditions
     * @return bool
     */
    public function delete($conditions)
    {

        //拼接sql
        $sql = " delete from  " . $this->getSource();
        $sql = rtrim($sql, ',');
        $sql .= " WHERE $conditions";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }


    /**
     * truncat table 慎用 也基本不会有这个权限
     * @date 2018-04-24
     */
    public function trancatTable()
    {
        $sql = " TRUNCATE TABLE   " . $this->getSource();
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据主键ID查找数据
     * @date 2018-04-24
     * @param int $pk 主键ID
     * @return object
     */
    public function getByPrimaryKey($pk)
    {
        $pk = intval($pk);
        if (0 >= $pk) {
            return false;
        }

        $condition = [
            'conditions' => $this->primaryKey . '=:id:',
            'bind' => [
                $this->primaryKey => $pk
            ]
        ];
        return self::findFirst($condition);
    }

    /**
     * 基础查询
     * @param $columns
     * @param $conditions
     * @param int $page
     * @param int $size
     * @param string $sort
     * @param string $group
     * @return array
     */
    public function findCondition($columns, $conditions, $page = 0, $size = 20, $sort = '', $group = '')
    {
        if (!$sort) {
            $sort = 'id desc';
        }

        if ($page) {
            $parameters = [
                'columns' => $columns,
                'conditions' => $conditions,
                'limit' => ['number' => $size, 'offset' => ($page - 1) * $size],
                'order' => $sort
            ];
        } else {
            $parameters = [
                'columns' => $columns,
                'conditions' => $conditions,
                'order' => $sort
            ];
        }

        if ($group) {
            $parameters['group'] = $group;
        }

        $ret = self::find($parameters);
        return $ret ? $ret->toArray() : [];
    }

    /**
     * 单条查询
     * @date 2018-04-25
     * @return array
     */
    public function findOne($columns, $conditions, $order = 0)
    {
        $parameters = [
            'columns' => $columns,
            'conditions' => $conditions,
        ];
        if ($order) {
            $parameters['order'] = $order;
        }
        $ret = self::findFirst($parameters);
        return $ret ? $ret->toArray() : [];
    }

    /**
     * 获取统计数据
     * @date 2018-04-25
     * @return array
     */
    public function getCount($conditions, $group = null)
    {
        if ($group) {
            $parameters = [
                'conditions' => $conditions,
                "group" => $group,
            ];
        } else {
            $parameters = [
                'conditions' => $conditions,
            ];
        }

        $ret = self::count($parameters);
        return $ret;
    }

    /**
     * 封装phalcon model的update方法，实现仅更新数据变更字段，而非所有字段更新
     * @param array|null $data
     * @param null $whiteList
     * @return mixed
     * @date 2018/5/4 13:45
     */
    public function iupdate(array $data=null, $whiteList=null){
        if(count($data) > 0){
            $attributes = $this -> getModelsMetaData() -> getAttributes($this);
            $this -> skipAttributesOnUpdate(array_diff($attributes, array_keys($data)));
        }
        return $this->update($data, $whiteList);
    }

}
