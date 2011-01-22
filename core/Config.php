<?php

namespace 'pixelpost';

/**
 * Configuration support 
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
final class Config extends \ArrayObject
{
    /**
     * @var Config The singleton of that class is stored here
     */
    private static $_instance;

    /**
     * @var string The filename where the configuration is read to.
     */
    private static $_file;

    /**
     * You shoudn't use this contructor directly, You must use create() method.
     *
     * @throws Error
     */
    final public function __construct()
    {
        if ( ! is_null(self::$_instance) )
        {
            throw Error::create();
        }

        parent::__construct(array(), \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Returns an unique instance of Config class containing the configuration data
     *
     * @return Config
     */
    public static function create()
    {
        self::$_instance = self::$_instance ?: new static;

        return self::$_instance;
    }

    /**
     * Load the $filename as a json encoded configuration file. All data in the
     * configuration file can be accessed by getting the instance of Config 
     * class. 
     * Keep in memory the filename in $_file private static var.
     *
     * @throws Error
     *
     * @param string $filename The configuration file you want load.
     */
    public static function load($filename)
    {
        Filter::assume_string($filename);

        if ( ! file_exists($filename) )
        {
            throw Error::create();
        }

        if ( false !== $content = file_get_contents($filename) )
        {
            throw Error::create();
        }

        $conf = json_decode($content, true);

        if ( json_last_error() != \JSON_ERROR_NONE )
        {
            throw Error::create();
        }

        static::create()->exchangeArray($conf);

        self::$_file = $filename;
    }

    /**
     * Backup the actual configuration in the file referenced by $_file.
     *
     * @return bool
     */
    public function save()
    {
        $data = json_encode($this, JSON_FORCE_OBJECT | JSON_HEX_TAG | 
                                   JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        return (bool) file_put_contents(self::$_file, $data, LOCK_EX);
    }
}
