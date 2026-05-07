<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think\cache;

interface Driver
{
    public function get($name, $default = null);
    public function set($name, $value, $expire = null);
    public function delete($name);
    public function clear();
    public function has($name);
}

class PhpFile
{
    protected $options = [
        'path' => RUNTIME_PATH,
        'ext' => '.php',
        'prefix' => '',
    ];
    
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }
    
    public function get($name, $default = null)
    {
        $filename = $this->getFilename($name);
        if (file_exists($filename)) {
            $content = include $filename;
            if ($content && ($content['expire'] == 0 || $content['expire'] > time())) {
                return $content['value'];
            }
        }
        return $default;
    }
    
    public function set($name, $value, $expire = 0)
    {
        $filename = $this->getFilename($name);
        $dir = dirname($filename);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $data = [
            'value' => $value,
            'expire' => $expire > 0 ? time() + $expire : 0,
        ];
        
        return file_put_contents($filename, '<?php return ' . var_export($data, true) . ';') !== false;
    }
    
    public function delete($name)
    {
        $filename = $this->getFilename($name);
        return file_exists($filename) ? unlink($filename) : true;
    }
    
    public function clear()
    {
        $files = glob($this->options['path'] . '*' . $this->options['ext']);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    public function has($name)
    {
        $filename = $this->getFilename($name);
        return file_exists($filename);
    }
    
    protected function getFilename($name)
    {
        $name = md5($name);
        return $this->options['path'] . $name . $this->options['ext'];
    }
}

class File extends PhpFile
{
}
