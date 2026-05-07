<?php
// +----------------------------------------------------------------------

namespace think;

class View
{
    protected $vars = [];
    protected $replace = [];
    
    public static function instance($vars = [], $replace = [])
    {
        $view = new static();
        $view->vars = $vars;
        $view->replace = $replace;
        return $view;
    }
    
    public function fetch($template = '', $vars = [], $replace = [], $code = 200)
    {
        $vars = array_merge($this->vars, $vars);
        $replace = array_merge($this->replace, $replace);
        
        if (empty($template)) {
            $request = Request::instance();
            $controller = $request->param('_c', 'index');
            $action = $request->param('_a', 'index');
            $template = strtolower($controller . '/' . $action);
        }
        
        $templateFile = $this->parseTemplate($template);
        
        if (!file_exists($templateFile)) {
            throw new \Exception('Template not exists: ' . $templateFile);
        }
        
        ob_start();
        extract($vars);
        include $templateFile;
        $content = ob_get_clean();
        
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        
        return Response::create($content, 'html', $code)->send();
    }
    
    protected function parseTemplate($template)
    {
        if (strpos($template, '/') === false) {
            $request = Request::instance();
            $controller = $request->param('_c', 'index');
            $template = strtolower($controller . '/' . $template);
        }
        
        $template = str_replace('.', '/', $template);
        
        if (strpos($template, '@')) {
            list($module, $template) = explode('@', $template);
            return APP_PATH . $module . '/view/' . $template . '.html';
        }
        
        return APP_PATH . 'admin/view/' . $template . '.html';
    }
    
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }
        return $this;
    }
}
