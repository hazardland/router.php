<?php

class app
{
    public static $config = [
        'path' => [],
        'locale' => ['en']
    ];

    public static $active;

    private static $route = [];
    private static $find = [];
    private static $default;
    public static $request;

    public static $locale;

    public $name;
    public $path;
    public $callback;
    public $method;
    public $pattern;
    public $type = [];
    public $input = [];

    public function __construct ($path, $callback, $method=null)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = strtolower($method);
    }
    public static function add ($path, $callback, $method=null)
    {
        $route = new app ($path, $callback, $method);
        self::$route[] = $route;
        if ($path=='/')
        {
            self::$default = $route;
        }
        return $route;
    }
    public static function get ($path, $callback)
    {
        return self::add ($path, $callback, 'get');
    }
    public static function post ($path, $callback)
    {
        return self::add ($path, $callback, 'post');
    }
    public static function run ($query=null, $method=null)
    {
        if (!isset(self::$config['locale']) || !is_array(self::$config['locale']))
        {
            self::$config['locale'] = ['en'];
        }
        $request = [
            'query' => null,
            'path' => null
        ];
        if ($query===null)
        {
            $query = isset($_REQUEST['query'])?$_REQUEST['query']:null;
        }
        if ($method===null)
        {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }
        if ($query!==null)
        {
            if (strpos($query,'/')!==false)
            {
                $locale = substr($query, 0, strpos($query,'/'));
                if (in_array($locale,self::$config['locale']))
                {
                    self::$locale = $locale;
                    $query = substr($query,strpos($query,'/')+1);
                }
            }
            else
            {
                $locale = $query;
                if (in_array($locale,self::$config['locale']))
                {
                    self::$locale = $locale;
                    $query = '/';
                }
                else
                {
                    $locale = null;
                }
            }
        }
        if (!isset($locale))
        {
            $locale = reset(self::$config['locale']);
        }
        if ($query===null || $query=='')
        {
            $query = '/';
        }

        self::$request = [
            'path' => $query,
            'locale' => $locale,
            'method' => $method
        ];
        debug (self::$request);
        debug (self::$route);
        foreach (self::$route as $route)
        {
            if ($route->active(self::$request))
            {
                debug ($route);
                self::$active = $route;
                self::$active->execute(self::$request);
                break;
            }
        }
    }
    public function active ($request)
    {
        //debug($this);
        //debug ($this->method);
        //debug($request->getMethod());
        //debug ($this->getPattern(), $this->path);
        if ($this->method!==null && $this->method!=$request['method'])
        {
            debug($request);
            return false;
        }

        if (
            ($request['path']==='/' && $this->path==='/') ||
            preg_match($this->pattern(), $request['path'])===1
           )
        {
            return true;
        }
        return false;
    }

    private function pattern ()
    {
        if ($this->pattern===null)
        {
            //first escape regex chars
            $pattern = preg_quote ($this->path,'/');

            //find variables in routes
            $matches = [];
            preg_match_all('/(\\\\\{[a-zA-Z0-9_]+\\\\\})+/', $pattern, $matches);

            //debug ($pattern);
            if (isset($matches[0]) && count($matches[0]))
            {
                foreach ($matches[0] as $match)
                {
                    $name = substr($match,2,-2);
                    $pattern = str_replace ($match,self::regex(isset($this->type[$name])?$this->type[$name]:''),$pattern);
                    $this->input[] = $name;
                }
            }
            //debug ($matches, $this->path);
            $this->pattern = '/^'.$pattern.'$/';
        }
        return $this->pattern;
    }
    public function input ($path)
    {
        if ($path==='/' && $this->path==='/')
        {
            return [];
        }
        $matches = [];
        preg_match_all($this->pattern(), $path, $matches, PREG_SET_ORDER);
        if (!isset($matches[0]) || count($matches[0])<=1) return [];
        $result = [];
        foreach ($matches[0] as $key=>$value)
        {
            if ($key==0) continue;
            $result[$this->input[$key-1]] = $value;
        }
        return $result;
    }
    private static function regex ($type)
    {
        if ($type=='int' || $type=='integer')
        {
            return '([0-9]+)';
        }
        if ($type=='float')
        {
            return '([0-9\.]+)';
        }
        if ($type=='bool' || $type=='boolean')
        {
            return '(0|false|False|FALSE|1|true|True|TRUE)';
        }
        if ($type=='email')
        {
            return '([^\.][a-zA-Z0-9_\-.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+[^\.])';
        }
        return '([a-zA-Z0-9@\-\.\;\,\_]+)';
    }

    public function type ($name, $type)
    {
        $this->reset ();
        $this->type[$name] = $type;
        return $this;
    }

    public function name ($name)
    {
        $this->name = $name;
        self::$find [$name] = $this;
        return $this;
    }

    private function reset ()
    {
        $this->type = [];
        $this->pattern = null;
    }

    public function execute ($request)
    {
        $result = null;
        if (is_object($this->callback))
        {
            $result = call_user_func_array ($this->callback, $this->input($request['path']));
        }
        return $result;
    }

    public static function url ($path, $args=[], $locale=null)
    {
        if (is_string($args))
        {
            $locale = $args;
            $args[] = null;
        }
        if (isset(self::$find[$path]))
        {
            $result = self::$find[$path]->path;
            if (is_array($args) && count($args)>0)
            {
                foreach ($args as $key=>$value)
                {
                    $result = str_replace('{'.$key.'}', $value, $result);
                }
            }
            $path = $result;
        }
        $resource = ($locale!==null?$locale.'/':(self::locale()!==null?self::locale().'/':'')).$path;
        return dirname($_SERVER['SCRIPT_NAME']).($resource!==null?'/'.$resource:'');
    }

    public static function locale ()
    {
        return self::$locale;
    }

    public static function view ($__name, $__values=[])
    {
        if (is_array($__values) && count($__values))
        {
            foreach ($__values as $__key => &$__value)
            {
                ${$__key} = $__value;
            }
        }
        include self::$config['path']['view'].$__name.'.php';
    }

    public static function script ($src)
    {
        return "<script src=\"".self::url($src)."\"></script>";
    }
    public static function style ($href)
    {
        return "<link rel=\"stylesheet\" href=\"".self::url($href)."\" />";
    }


}
