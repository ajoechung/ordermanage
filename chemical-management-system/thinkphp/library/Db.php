<?php
// +----------------------------------------------------------------------

namespace think;

class Db
{
    protected static $connections = [];
    
    public static function connect($config = [], $name = 'default')
    {
        if (!isset(self::$connections[$name])) {
            self::$connections[$name] = new DbConnection($config);
        }
        return self::$connections[$name];
    }
    
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([static::connect(), $method], $args);
    }
}

class DbConnection
{
    protected $config = [];
    protected $pdo;
    protected $statement;
    protected $tablePrefix = '';
    
    public function __construct($config = [])
    {
        $this->config = array_merge([
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => '',
            'username' => 'root',
            'password' => '',
            'hostport' => '3306',
            'charset' => 'utf8mb4',
            'prefix' => '',
        ], $config);
        
        $this->tablePrefix = $this->config['prefix'];
        $this->connect();
    }
    
    protected function connect()
    {
        try {
            $dsn = "mysql:host={$this->config['hostname']};port={$this->config['hostport']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->pdo = new \PDO($dsn, $this->config['username'], $this->config['password']);
            $this->pdo->exec("SET NAMES '{$this->config['charset']}'");
        } catch (\PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function name($name)
    {
        return (new Query($this))->table($this->tablePrefix . $name);
    }
    
    public function table($name)
    {
        return (new Query($this))->table($this->parseTable($name));
    }
    
    public function query($sql, $bind = [])
    {
        $this->statement = $this->pdo->prepare($sql);
        $this->statement->execute($bind);
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function execute($sql, $bind = [])
    {
        $this->statement = $this->pdo->prepare($sql);
        return $this->statement->execute($bind);
    }
    
    public function getLastInsID()
    {
        return $this->pdo->lastInsertId();
    }
    
    public function getPdo()
    {
        return $this->pdo;
    }
    
    protected function parseTable($table)
    {
        if (strpos($table, $this->tablePrefix) !== 0) {
            $table = $this->tablePrefix . $table;
        }
        return $table;
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array([$this->name(''), $method], $args);
    }
}

class Query
{
    protected $connection;
    protected $table;
    protected $where = [];
    protected $fields = '*';
    protected $order;
    protected $limit;
    protected $sql;
    protected $bind = [];
    protected $lastSql;
    
    public function __construct(DbConnection $connection)
    {
        $this->connection = $connection;
    }
    
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }
    
    public function where($field, $op = null, $value = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $val) {
                if (is_array($val)) {
                    $this->where[] = [$key, $val[0], $val[1]];
                } else {
                    $this->where[] = [$key, '=', $val];
                }
            }
        } elseif ($value === null) {
            $this->where[] = [$field, '=', $op];
        } else {
            $this->where[] = [$field, $op, $value];
        }
        return $this;
    }
    
    public function field($fields)
    {
        $this->fields = $fields;
        return $this;
    }
    
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }
    
    public function limit($limit, $offset = null)
    {
        if ($offset !== null) {
            $this->limit = "$offset, $limit";
        } else {
            $this->limit = $limit;
        }
        return $this;
    }
    
    public function select()
    {
        $this->sql = $this->buildSelect();
        $result = $this->connection->query($this->sql, $this->bind);
        $this->lastSql = $this->sql;
        return $result;
    }
    
    public function find()
    {
        $this->limit(1);
        $result = $this->select();
        return $result ? $result[0] : null;
    }
    
    public function column($field = null)
    {
        $result = $this->field($field ?: '*')->select();
        if ($result) {
            return array_column($result, $field ?: array_keys($result[0])[0]);
        }
        return [];
    }
    
    public function value($field)
    {
        $result = $this->field($field)->limit(1)->select();
        return $result ? ($result[0][$field] ?? null) : null;
    }
    
    public function count($field = '*')
    {
        $this->fields = "COUNT($field) as tp_count";
        $result = $this->find();
        return $result ? (int)$result['tp_count'] : 0;
    }
    
    public function sum($field)
    {
        $this->fields = "SUM($field) as tp_sum";
        $result = $this->find();
        return $result ? (float)$result['tp_sum'] : 0;
    }
    
    public function insert($data)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $this->bind = $values;
        
        $fieldsStr = '`' . implode('`,`', $fields) . '`';
        $valuesStr = implode(',', array_fill(0, count($values), '?'));
        
        $this->sql = "INSERT INTO {$this->table} ($fieldsStr) VALUES ($valuesStr)";
        $this->connection->execute($this->sql, $this->bind);
        $this->lastSql = $this->sql;
        
        return $this->connection->getLastInsID();
    }
    
    public function update($data)
    {
        $sets = [];
        $values = [];
        
        foreach ($data as $key => $val) {
            $sets[] = "`$key` = ?";
            $values[] = $val;
        }
        
        $this->bind = $values;
        $setsStr = implode(',', $sets);
        
        $where = $this->buildWhere();
        $this->sql = "UPDATE {$this->table} SET $setsStr $where";
        $this->connection->execute($this->sql, $this->bind);
        $this->lastSql = $this->sql;
        
        return $this->statement ? $this->statement->rowCount() : 0;
    }
    
    public function delete()
    {
        $where = $this->buildWhere();
        $this->sql = "DELETE FROM {$this->table} $where";
        $this->connection->execute($this->sql, $this->bind);
        $this->lastSql = $this->sql;
        return $this->statement ? $this->statement->rowCount() : 0;
    }
    
    public function paginate($perPage = 15)
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $perPage;
        
        $count = $this->count();
        
        $this->limit($perPage, $offset);
        $list = $this->select();
        
        return [
            'total' => $count,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($count / $perPage),
            'data' => $list,
        ];
    }
    
    protected function buildSelect()
    {
        $fields = $this->fields ?: '*';
        $where = $this->buildWhere();
        $order = $this->order ? "ORDER BY {$this->order}" : '';
        $limit = $this->limit ? "LIMIT {$this->limit}" : '';
        
        return "SELECT $fields FROM {$this->table} $where $order $limit";
    }
    
    protected function buildWhere()
    {
        if (empty($this->where)) {
            return '';
        }
        
        $where = [];
        foreach ($this->where as $item) {
            list($field, $op, $value) = $item;
            $this->bind[] = $value;
            $where[] = "`$field` $op ?";
        }
        
        return 'WHERE ' . implode(' AND ', $where);
    }
    
    public function getLastSql()
    {
        return $this->lastSql;
    }
    
    public function join($table, $condition, $type = 'INNER')
    {
        $this->table .= " $type JOIN $table ON $condition";
        return $this;
    }
    
    public function group($field)
    {
        $this->table .= " GROUP BY $field";
        return $this;
    }
}
