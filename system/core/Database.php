<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use PDO;
use PDOException;
use core\exceptions\DatabaseException;

/**
 * Provides wrappers for PDO methods
 */
class Database extends PDO
{

    /**
     * Sets up the database connection
     * Database constructor.
     * @param array $config
     * @throws DatabaseException
     */
    public function __construct(array $config)
    {
        $dns = "{$config['type']}:host={$config['host']};"
                . "port={$config['port']};dbname={$config['name']}";

        try {
            parent::__construct($dns, $config['user'], $config['password']);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exc) {
            // Throw custom exception to hide connection details in the message
            throw new DatabaseException('Could not connect to database');
        }
    }

    /**
     * Returns a single column
     * @param string $sql
     * @param array $params
     * @param integer $pos
     * @return mixed
     */
    public function fetchColumn($sql, array $params = array(), $pos = 0)
    {
        $sth = $this->run($sql, $params);
        return $sth->fetchColumn($pos);
    }

    /**
     * Runs a SQL query with an array of placeholders
     * @param string $sql
     * @param array $params
     * @return object
     */
    public function run($sql, array $params = array())
    {
        $sth = $this->prepare($sql);

        foreach ($params as $key => $value) {
            $key = is_numeric($key) ? $key + 1 : ":$key";
            $sth->bindValue($key, $value);
        }

        $sth->execute($params);
        return $sth;
    }

    /**
     * Returns a simple array of columns
     * @param string $sql
     * @param array $params
     * @param integer $pos
     * @return mixed
     */
    public function fetchColumnAll($sql, array $params = array(), $pos = 0)
    {
        $sth = $this->run($sql, $params);
        return $sth->fetchAll(PDO::FETCH_COLUMN, $pos);
    }

    /**
     * Returns a single array indexed by column name
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array
     */
    public function fetch($sql, array $params, array $options = array())
    {
        $sth = $this->run($sql, $params);
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        $this->prepareResult($result, $options);
        return empty($result) ? array() : (array) $result;
    }

    /**
     * Prepares a single result, e.g unserialize serialized data
     * @param mixed $data
     * @param array $options
     * @return null
     */
    protected function prepareResult(&$data, array $options)
    {
        if (empty($options['unserialize']) || empty($data)) {
            return null;
        }

        foreach ((array) $options['unserialize'] as $field) {
            $data[$field] = empty($data[$field]) ? array() : unserialize($data[$field]);
        }

        return null;
    }

    /**
     * Returns an array of database records
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array
     */
    public function fetchAll($sql, array $params, array $options = array())
    {
        $sth = $this->run($sql, $params);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->prepareResults($result, $options);
        return empty($result) ? array() : (array) $result;
    }

    /**
     * Prepares an array of results
     * @param array $results
     * @param array $options
     */
    protected function prepareResults(array &$results, array $options)
    {
        $reindexed = array();
        foreach ($results as &$result) {

            $this->prepareResult($result, $options);

            if (!empty($options['index'])) {
                $reindexed[$result[$options['index']]] = $result;
            }
        }

        if (!empty($options['index'])) {
            $results = $reindexed;
        }
    }

    /**
     * Performs an INSERT query
     * @param $table
     * @param array $data
     * @param bool $prepare
     * @return string
     */
    public function insert($table, array $data, $prepare = true)
    {
        if ($prepare) {
            $data = $this->prepareInsert($table, $data);
        }

        if (empty($data)) {
            return '0';
        }

        $keys = array_keys($data);
        $fields = implode(',', $keys);
        $values = ':' . implode(',:', $keys);

        $sth = $this->prepare("INSERT INTO $table ($fields) VALUES ($values)");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();
        return $this->lastInsertId();
    }

    /**
     * Returns an array of prepared values ready to insert into the database
     * @param string $table
     * @param array $data
     * @return array
     */
    public function prepareInsert($table, array $data)
    {
        $data += $this->getDefaultValues($table);
        return $this->filterValues($table, $data);
    }

    /**
     * Returns an array of default field values for the given table
     * @param string $table
     * @return array
     */
    public function getDefaultValues($table)
    {
        $scheme = $this->getScheme($table);

        if (empty($scheme['fields'])) {
            return array();
        }

        $values = array();
        foreach ($scheme['fields'] as $name => $info) {

            if (array_key_exists('default', $info)) {
                $values[$name] = $info['default'];
                continue;
            }

            if (!empty($info['serialize'])) {
                $values[$name] = array();
            }
        }

        return $values;
    }

    /**
     * Returns an array of database scheme
     * @param null $table
     * @return array|mixed
     * @throws DatabaseException
     */
    public function getScheme($table = null)
    {
        $data = include GC_CONFIG_DATABASE;

        if (empty($data)) {
            throw new DatabaseException('Failed to load database scheme');
        }

        if (isset($table)) {
            return empty($data[$table]) ? array() : $data[$table];
        }

        return $data;
    }

    /**
     * Filters an array of data according to existing scheme for the given table
     * @param string $table
     * @param array $data
     * @return array
     */
    protected function filterValues($table, array $data)
    {
        $scheme = $this->getScheme($table);

        if (empty($scheme['fields'])) {
            return array();
        }

        $values = array_intersect_key($data, $scheme['fields']);

        if (empty($values)) {
            return array();
        }

        foreach ($values as $field => &$value) {
            $this->filterValue($scheme, $values, $field, $value);
        }

        return $values;
    }

    /**
     * Filters a single item to be saved in the database
     * @param array $scheme
     * @param array $values
     * @param string $field
     * @param mixed $value
     */
    protected function filterValue($scheme, &$values, $field, &$value)
    {
        if (!empty($scheme['fields'][$field]['auto_increment'])) {
            unset($values[$field]); // Remove autoincremented fields
        }

        if (0 === strpos($scheme['fields'][$field]['type'], 'int')) {
            $value = intval($value); // Make value integer
        }

        if ($scheme['fields'][$field]['type'] === 'float') {
            $value = floatval($value); // Make value float
        }

        if (!empty($scheme['fields'][$field]['serialize']) && is_array($value)) {
            $value = serialize($value); // Serialize arrays
        }
    }

    /**
     * Performs a UPDATE query
     * @param $table
     * @param array $data
     * @param array $conditions
     * @param bool $filter
     * @return integer
     */
    public function update($table, array $data, array $conditions,
            $filter = true
    )
    {
        if ($filter) {
            $data = $this->filterValues($table, $data);
        }

        if (empty($data)) {
            return 0;
        }

        $farray = array();
        foreach ($data as $key => $value) {
            $farray[] = "$key=:$key";
        }

        $fields = implode(',', $farray);

        $carray = array();
        foreach ($conditions as $key => $value) {
            $carray[] = "$key=:$key";
        }

        $where = implode(' AND ', $carray);
        $stmt = $this->prepare("UPDATE $table SET $fields WHERE $where");

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Performs a DELETE query
     * @param string $table
     * @param array $conditions
     * @return integer
     */
    public function delete($table, array $conditions)
    {
        if (empty($conditions)) {
            return 0;
        }

        $carray = array();
        foreach ($conditions as $key => $value) {
            $carray[] = "$key=:$key";
        }

        $where = implode(' AND ', $carray);
        $stmt = $this->prepare("DELETE FROM $table WHERE $where");

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Creates tables using an array of scheme data
     * @param array $tables
     * @return bool
     */
    public function import(array $tables)
    {
        $imported = 0;
        foreach ($tables as $table => $data) {

            $sql = $this->getSqlCreateTable($table, $data);

            if ($this->query($sql) !== false) {
                $imported++;
            }

            $alter = $this->getSqlAlterTable($table, $data);

            if (!empty($alter)) {
                $this->query($alter);
            }
        }

        return ($imported == count($tables));
    }

    /**
     * Returns a string with SQL query to create a table
     * @param string $table
     * @param array $data
     * @return string
     */
    protected function getSqlCreateTable($table, array $data)
    {
        $fields = $this->getSqlFields($data['fields']);
        $engine = isset($data['engine']) ? $data['engine'] : 'InnoDB';
        $collate = isset($data['collate']) ? $data['collate'] : 'utf8_general_ci';

        return "CREATE TABLE $table($fields) ENGINE=$engine CHARACTER SET utf8 COLLATE $collate";
    }

    /**
     * Returns a SQL that describes table fields.
     * Used to create tables
     * @param array $fields
     * @return string
     */
    protected function getSqlFields(array $fields)
    {
        $sql = array();
        foreach ($fields as $name => $info) {

            if (strpos($info['type'], 'text') !== false || $info['type'] === 'blob') {
                unset($info['default']);
            }

            $string = "{$info['type']}";

            if (isset($info['length'])) {
                $string .= "({$info['length']})";
            }

            if (!empty($info['not_null'])) {
                $string .= " NOT NULL";
            }

            if (isset($info['default'])) {
                $string .= " DEFAULT '{$info['default']}'";
            }

            if (!empty($info['auto_increment'])) {
                $string .= " AUTO_INCREMENT";
            }

            if (!empty($info['primary'])) {
                $string .= " PRIMARY KEY";
            }

            $sql[] = $name . ' ' . trim($string);
        }


        return implode(',', $sql);
    }

    /**
     * Returns a string with SQL query to alter a table
     * @param string $table
     * @param array $data
     * @return string
     */
    protected function getSqlAlterTable($table, array $data)
    {
        return empty($data['alter']) ? '' : "ALTER TABLE $table {$data['alter']}";
    }

}