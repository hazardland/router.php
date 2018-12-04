<?php

class app
{
    public static $config = [
        'path' => [],
        'locale' => ['en']
    ];

    private static $route;
    private static $default;
    public static $request;

    public static $locale;

    public $path;
    public $callback;
    public $method;

    public function __construct ($path, $callback, $method=null)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
    }
    public static function add ($path, $callback, $method=null)
    {
        $route = new app ($path, $callback, $method);
        self::$route[] = $route;
        if ($action->path=='/')
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
            $method = $_SERVER['REQUEST_METHOD'];
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
    }
        public function active (Request $request)
        {
            //debug ($this->method);
            //debug($request->getMethod());
            //debug ($this->getPattern(), $this->path);
            if ($this->method!==null && $this->method!=$request->getMethod())
            {
                return false;
            }

            if (
                ($request->getPath()==='/' && $this->path==='/') ||
                preg_match($this->getPattern(), $request->getPath())===1
               )
            {
                //Maybe filter compiration must be before regex match?
                //If it will be cost effective
                if (is_array($this->filters) && count($this->filters))
                {
                    foreach ($this->filters as $name => $options)
                    {
                        $filter = Route::getFilter($name);
                        if (!$filter($this, $request, $options))
                        {
                            return false;
                        }
                    }
                }
                return true;
            }
            return false;
        }


}
