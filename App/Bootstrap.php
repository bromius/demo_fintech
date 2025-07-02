<?php

require_once __DIR__ . '/Lib/Slim/Slim.php';
require_once __DIR__ . '/Lib/Symfony/Component/VarDumper/VarDumper.php';
require_once __DIR__ . '/Lib/Medoo/src/Medoo.php';

require_once __DIR__ . '/Helpers/Config.php';

$cfg = Config::getInstance()->load([
    'path'   => __DIR__ . '/config/paths.php',
    'app'   => __DIR__ . '/config/app.php',
    'db'    => __DIR__ . '/config/database.php',
]);

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', $cfg->get('app.mode') == 'development');

date_default_timezone_set($cfg->get('app.timezone'));

/**
 * Application initialization and configuration handler
 * 
 * Manages the application lifecycle including autoloading,
 * directory setup, and framework initialization
 */
class Bootstrap 
{
    /**
     * Singleton instance of the bootstrap class
     * 
     * @var Bootstrap
     */
    private static $instance;

    /**
     * Slim application instance
     * 
     * @var \Slim\Slim
     */
    private $app;

    /**
     * Returns the singleton instance of the bootstrap class
     * 
     * @return Bootstrap Current instance
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Gets the Slim application instance
     * 
     * @return \Slim\Slim Configured application instance
     */
    public function app() 
    {
        return $this->app;
    }

    /**
     * Configures the autoloader for application classes
     */
    protected function setAutoload()
    {
        \Slim\Slim::registerAutoloader();

        spl_autoload_register(function ($className) {
            $className = ltrim($className, '\\');
            $fileName = '';
            $namespace = '';

            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }

            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            $paths = [
                __DIR__ . '/../',
                __DIR__ . '/Lib/',
                __DIR__ . '/Helpers/'
            ];

            foreach ($paths as $path) {
                $fullPath = $path . $fileName;
                if (file_exists($fullPath)) {
                    require_once $fullPath;
                    return;
                }
            }
        });
    }

    /**
     * Ensures required application directories exist
     */
    protected function initDirectories()
    {
        $requiredDirectories = [
            cfg('path.cache'),
            dirname(cfg('path.log')),
        ];

        foreach ($requiredDirectories as $dir) {
            \File::ensureDirectoryExists($dir);
        }
    }

    /**
     * Initializes the Slim application with configuration
     */
    protected function initApp()
    {
        $this->app = new \Slim\Slim([
            'templates.path' => cfg('path.templates'),
            'debug' => cfg('app.debug'),
            'mode' => cfg('app.mode'),

            'log.enabled' => cfg('app.log_enabled'),
            'log.level' => \Slim\Log::DEBUG,
            'log.writer' => new \Slim\LogWriter(fopen(cfg('path.log'), 'a')),
        ]);

        $view = $this->app->view();
        $view->parserOptions = [
            'debug' => cfg('app.debug'),
            'cache' => cfg('path.cache'),
        ];
    }

    /**
     * Loads global helper functions
     */
    protected function initGlobals()
    {
        require_once __DIR__ . '/Helpers/Globals.php';
    }

    /**
     * Loads application route definitions
     */
    protected function initRoutes()
    {
        require_once __DIR__ . '/config/routes.php';
    }

    /**
     * Runs the application initialization sequence
     */
    public function run() 
    {
        $this->setAutoload();
        $this->initGlobals();

        $this->initDirectories();
        $this->initApp();
        $this->initRoutes();

        $this->app->run();
    }
    
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}