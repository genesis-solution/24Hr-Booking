<?php
/**
* The main application class.
* Provides the DI container functionality.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core;

class App
{
    protected static $registered = array();

    protected static $binded = array();

    /**
     * Binds some entity to the app container.
     *
     * @param string $key
     * @param mixed $value
     * @access public
     * @return void
     */
    public static function bind($key, $value)
    {
        if(!array_key_exists($key, static::$binded))
        {
            static::$binded[$key] = $value;
        }
        else
        {
            throw new \Exception(sprintf(esc_html__('The key %s is already bound.', 'milenia-app-textdomain'), $key));
        }
    }

    /**
     * Returns registered entity from the app container by key.
     *
     * @param string $key
     * @access public
     * @return mixed
     */
    public static function get($key)
    {
        if(!array_key_exists($key, static::$binded))
        {
            throw new \Exception(sprintf(esc_html__('The key %s has not been bound.', 'milenia-app-textdomain'), $key));
        }

        return static::$binded[$key];
    }

    /**
     * Registers specified callback function in the app container.
     * Useful to get some objects during runtime.
     *
     * @param string $key
     * @param Closure $callback
     * @access public
     * @return void
     */
    public static function register($key, Closure $callback)
    {
        if(array_key_exists($key, static::$binded))
        {
            throw new \Exception(sprintf(esc_html__('The key %s is already registered.', 'milenia-app-textdomain'), $key));
        }
        else {
            static::$registered[$key] = $callback;
        }
    }

    /**
     * Returns a value from the callback that has been registered with specified key.
     *
     * @param string $key
     * @param array $params
     * @access public
     * @return mixed
     */
    public static function make($key, $params)
    {
        if(!array_key_exists($key, static::$registered))
        {
            throw new \Exception(sprintf(esc_html__('The key %s has not been registered.', 'milenia-app-textdomain'), $key));
        }

        return call_user_func_array(static::$registered[$key], $params);
    }
}
?>
