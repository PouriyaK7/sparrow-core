<?php


namespace Sparrow;


class Route
{

    public static array $args = [],
        $middlewares = [
            'before' => [],
            'after' => []
        ];

    /**
     * @var string $filePath
     */

    public static string $filePath = '';

    /**
     * @return int|void
     * @throws \SmartyException
     */

    public static function view()
    {
        $route = self::route($_SERVER['REQUEST_URI']);
        if ($route === false)
            return http_response_code(404);
        if (is_file(CONTROLLER_PATH . self::$filePath . '.php')) {
            self::$middlewares['before'][] = '../app/middlewares/before.php';
            foreach (self::$middlewares['before'] as $middleware)
                require $middleware;

            $controller = require CONTROLLER_PATH . self::$filePath . '.php';
            if ($controller === false)
                return;

            foreach (self::$middlewares['after'] as $middleware)
                require $middleware;
        }

        if (is_file(VIEW_PATH . self::$filePath . '.tpl')) {
            $smarty = new \SmartyBC();

            $smarty->setTemplateDir(VIEW_PATH);
            $smarty->setCompileDir('../resources/smarty/compiled');
            $smarty->setCacheDir('../resources/smarty/cache');
            $smarty->setConfigDir('../resources/smarty/config');

            if (isset($assigns))
                foreach ($assigns as $key => $assign)
                    $smarty->assign($key, $assign);

            $smarty->assign('Setting', new Setting());


            $smarty->display(VIEW_PATH . self::$filePath . '.tpl');
        }
    }

    /**
     * @param $uri
     * @return array|false|mixed[]
     */

    public static function route($uri) {
        $uri = rtrim($uri, '/');
        $args = '';
        do {
            $pos = strrpos($uri, '/');
            if (is_file(VIEW_PATH . $uri . '.tpl'))
                self::$filePath = $uri;
            elseif (is_file(VIEW_PATH . $uri . '/index.tpl') && !self::$filePath)
                self::$filePath = $uri . '/index';
            elseif (is_file(CONTROLLER_PATH . $uri . '.php'))
                self::$filePath = $uri;
            elseif (is_file(CONTROLLER_PATH . $uri . '/index.php'))
                self::$filePath = $uri . '/index';
            else
                if (!is_dir(CONTROLLER_PATH . substr($uri, $pos)) || !CONTROLLER_PATH . is_file(substr($uri, $pos)))
                    $args .= substr($uri, $pos);

            if (is_dir(MIDDLEWARE_PATH . $uri)) {
                if (is_file(MIDDLEWARE_PATH . $uri . '/after.php'))
                    self::$middlewares['after'][] = MIDDLEWARE_PATH . $uri . '/after.php';
                if (is_file(MIDDLEWARE_PATH . $uri . '/before.php'))
                    self::$middlewares['before'][] = MIDDLEWARE_PATH . $uri . '/before.php';
            }
            $uri = substr($uri, 0, $pos);
        }
        while($pos);

        if (self::$filePath == '' && is_file(VIEW_PATH . 'index.tpl') && $uri == '/')
            self::$filePath = 'index';
        elseif (!self::$filePath)
            return false;

        self::$args = $args?explode('/', trim($args, '/')):[];

        return self::$args;
    }

    public static function getVar(?int $index = null) {
        if (!is_null($index))
            return self::$args[$index] ?? null;
        return self::$args;
    }
}