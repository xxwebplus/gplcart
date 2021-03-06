<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use PDO;
use PDOException;
use gplcart\core\exceptions\Database as DatabaseException;

/**
 * Provides methods to work with the database
 * @todo Sqlite compatibility
 */
class Database
{

    /**
     * PDO class instance
     * @var \PDO $pdo
     */
    protected $pdo;

    /**
     * An array of collected queries
     * @var array
     */
    protected $logs = array();

    /**
     * An array of database scheme
     * @var array
     */
    protected $scheme = array();

    /**
     * Set up database connection
     * @param mixed $config
     * @throws DatabaseException
     * @return $this
     */
    public function init($config)
    {
        $this->pdo = null;

        $dns = '';
        if (is_array($config)) {
            $config += array('user' => null, 'password' => null);
            $dns = "{$config['type']}:host={$config['host']};port={$config['port']};dbname={$config['name']}";
        } else if (is_string($config)) {
            $dns = $config;
        } else if ($config instanceof \PDO) {
            $this->pdo = $config;
            return $this;
        }

        try {
            // Use pipe for merging fields - sqlite compatibility
            $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='PIPES_AS_CONCAT'");
            $this->pdo = new PDO($dns, $config['user'], $config['password'], $options);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            $this->pdo = null;
            throw new DatabaseException('Cannot connect to database: ' . $ex->getMessage());
        }

        return $this;
    }

    /**
     * Whether the database is ready
     * @return bool
     */
    public function isInitialized()
    {
        return isset($this->pdo);
    }

    /**
     * Returns PDO instance
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Returns an array of collected query logs
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @param string $statement
     * @return \PDOStatement|false
     */
    public function query($statement)
    {
        $result = $this->pdo->query($statement);
        $this->logs[] = $statement;
        return $result;
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $statement
     * @return integer
     */
    public function exec($statement)
    {
        $result = $this->pdo->exec($statement);
        $this->logs[] = $statement;
        return $result;
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
        return $this->run($sql, $params)->fetchColumn($pos);
    }

    /**
     * Runs a SQL query with an array of placeholders
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function run($sql, array $params = array())
    {
        $sth = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $key = is_numeric($key) ? $key + 1 : ":$key";
            $sth->bindValue($key, $value);
        }

        $sth->execute($params);
        $this->logs[] = $sql;
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
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_COLUMN, $pos);
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
     * Prepares a single result
     * @param mixed $data
     * @param array $options
     */
    protected function prepareResult(&$data, array $options)
    {
        if (!empty($options['unserialize']) && !empty($data)) {
            foreach ((array) $options['unserialize'] as $field) {
                $data[$field] = empty($data[$field]) ? array() : unserialize($data[$field]);
            }
        }
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
        $result = $this->run($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
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

        $this->run("INSERT INTO $table ($fields) VALUES ($values)", $data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Performs a UPDATE query
     * @param $table
     * @param array $data
     * @param array $conditions
     * @param bool $filter
     * @return integer
     */
    public function update($table, array $data, array $conditions, $filter = true)
    {
        if ($filter) {
            $data = $this->filterValues($table, $data);
        }

        if (empty($data)) {
            return 0;
        }

        $farray = array();
        foreach (array_keys($data) as $key) {
            $farray[] = "$key=:$key";
        }

        $carray = array();
        foreach (array_keys($conditions) as $key) {
            $carray[] = "$key=:$key";
        }

        $fields = implode(',', $farray);
        $sql = "UPDATE $table SET $fields WHERE " . implode(' AND ', $carray);

        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        $this->logs[] = $sql;
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
        foreach (array_keys($conditions) as $key) {
            $carray[] = "$key=:$key";
        }

        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $carray);
        return $this->run($sql, $conditions)->rowCount();
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
     * @staticvar array $scheme
     * @param string|null $table
     * @return array
     */
    public function getScheme($table = null)
    {
        $default = (array) gplcart_config_get(GC_FILE_CONFIG_DATABASE);
        $scheme = array_merge($this->scheme, $default);

        if (isset($table)) {
            return empty($scheme[$table]) ? array() : $scheme[$table];
        }

        return $scheme;
    }

    /**
     * Add an array of database scheme data
     * @param array $data
     */
    public function addScheme(array $data)
    {
        $this->scheme = array_merge($data, $this->scheme);
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
            unset($values[$field]);
        }

        if (strpos($scheme['fields'][$field]['type'], 'int') === 0) {
            $value = intval($value);
        }

        if ($scheme['fields'][$field]['type'] === 'float') {
            $value = floatval($value);
        }

        if (!empty($scheme['fields'][$field]['serialize']) && is_array($value)) {
            $value = serialize($value);
        }
    }

    /**
     * Drop a table
     * @param string $table
     */
    public function deleteTable($table)
    {
        $this->query("DROP TABLE IF EXISTS $table");
    }

    /**
     * Check if a table already exists
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        $result = $this->query("SHOW TABLES LIKE " . $this->pdo->quote($table));
        return $result->rowCount() > 0;
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

        return $imported == count($tables);
    }

    /**
     * Install a database table using the scheme
     * @param string $table
     * @param array $scheme
     * @return boolean|string
     */
    public function importScheme($table, array $scheme)
    {
        if ($this->tableExists($table)) {
            return 'Table already exists';
        }

        if (!$this->import($scheme)) {
            $this->deleteTable($table);
            return 'An error occurred while importing the database table';
        }

        return true;
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

            if (!empty($info['primary'])) {
                $string .= " PRIMARY KEY";
            }

            if (!empty($info['auto_increment'])) {
                $string .= " /*!40101 AUTO_INCREMENT */"; // SQLite will ignore this comment
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
