<?php
// +----------------------------------------------------------------------

namespace think;

class Log
{
    protected static $log = [];
    
    public static function write($message, $type = 'log', $destination = '')
    {
        $time = date('Y-m-d H:i:s');
        $logFile = $destination ?: RUNTIME_PATH . 'logs/' . date('Ym') . '.log';
        
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $content = "[{$time}] {$type}: {$message}\n";
        file_put_contents($logFile, $content, FILE_APPEND);
        
        self::$log[] = $content;
    }
    
    public static function log($message)
    {
        return self::write($message, 'log');
    }
    
    public static function error($message)
    {
        return self::write($message, 'error');
    }
    
    public static function info($message)
    {
        return self::write($message, 'info');
    }
}
