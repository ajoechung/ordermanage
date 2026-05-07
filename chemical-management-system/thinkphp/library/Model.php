<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think;

class Model
{
    protected $name;
    protected $table;
    protected $pk = 'id';
    protected $connection;
    
    public function __construct()
    {
        if (empty($this->name)) {
            $this->name = basename(str_replace('\\', '/', get_class($this)));
        }
        
        if (empty($this->table)) {
            $this->table = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $this->name));
        }
    }
    
    public static function __callStatic($method, $args)
    {
        $model = new static();
        $db = Db::name($model->table);
        
        if (method_exists($db, $method)) {
            return call_user_func_array([$db, $method], $args);
        }
        
        return call_user_func_array([$model, $method], $args);
    }
    
    public function __call($method, $args)
    {
        $db = Db::name($this->table);
        
        if (method_exists($db, $method)) {
            return call_user_func_array([$db, $method], $args);
        }
        
        throw new \Exception('Method not exists: ' . $method);
    }
    
    public function find($id = null)
    {
        if ($id !== null) {
            return Db::name($this->table)->where($this->pk, $id)->find();
        }
        return Db::name($this->table)->find();
    }
    
    public function select()
    {
        return Db::name($this->table)->select();
    }
    
    public function save($data = [])
    {
        if (empty($data)) {
            $data = $_POST;
        }
        
        $pk = $this->pk;
        
        if (isset($data[$pk]) && !empty($data[$pk])) {
            return Db::name($this->table)->where($pk, $data[$pk])->update($data);
        } else {
            return Db::name($this->table)->insert($data);
        }
    }
    
    public function delete($id = null)
    {
        if ($id !== null) {
            return Db::name($this->table)->where($this->pk, $id)->delete();
        }
        return Db::name($this->table)->delete();
    }
}
