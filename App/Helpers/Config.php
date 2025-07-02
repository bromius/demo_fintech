<?php

class Config
{
    /**
     * @var Config Singleton instance
     */
    private static $instance;

    /**
     * @var array Loaded configuration data
     */
    private $config = [];

    /**
     * Get the configuration instance
     * 
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Load configuration files
     * 
     * @param array $configFiles Array of file paths keyed by config name
     * @return Config
     * @throws Exception If config file not found
     */
    public function load($configFiles)
    {
        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                $this->config[$key] = require_once $file;
            } else {
                throw new Exception("Config file not found: {$file}");
            }
        }
        return $this;
    }

    /**
     * Get configuration value
     * 
     * @param string $key Dot notation key (e.g. 'db.host')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value
     * 
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @return Config
     */
    public function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
        return $this;
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Dot notation key
     * @return bool
     */
    public function has($key)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }

    /**
     * Get all configuration data
     * 
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}