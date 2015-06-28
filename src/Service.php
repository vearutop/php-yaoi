<?php
namespace Yaoi;

use Closure;
use Yaoi\Service\Exception;
use Yaoi\String\Utils;
use Yaoi\Service\Settings;

/**
 * Class Service
 * @package Yaoi
 */

abstract class Service extends BaseClass
{
    const PRIMARY = 'primary';
    const FALLBACK = 'fallback';

    private static $registry = array();
    public static function register($identifier, $settings) {
        $class = get_called_class();
        self::$registry[$class][$identifier] = $settings;
    }

    private static $instances = array();

    public function getSettings() {
        return $this->settings;
    }

    /**
     * @return Settings
     */
    public static function createSettings() {
        $className = static::getSettingsClassName();
        return new $className;
    }

    /**
     * @param null $settings
     * @return Service\Settings
     * @throws Service\Exception
     */
    protected static function settings($settings)
    {
        $settingsClassName = static::getSettingsClassName();

        if (null === $settingsClassName) {
            $settingsClassName = Settings::className();

            if (null === $settings) {
                return new Settings();
            }
        }

        if ($settings instanceof $settingsClassName) {
            return $settings;
        }

        if ($settings instanceof Closure) {
            $settings = $settings();
            if (!$settings instanceof $settingsClassName) {
                throw new Service\Exception('Closure should return ' . $settingsClassName . ' instance',
                    Service\Exception::SETTINGS_REQUIRED);
            }
            return $settings;
        }

        if (is_string($settings)) {
            $settings = new $settingsClassName($settings);
            return $settings;
        }

        throw new Service\Exception('Invalid argument', Service\Exception::SETTINGS_REQUIRED);
    }


    /**
     * @param Settings|string|Closure|null $settings
     * @throws Service\Exception
     */
    public function __construct($settings = null)
    {
        if (null !== $settings) {
            $this->settings = static::settings($settings);
        } else {
            $this->settings = new Settings();
        }
    }


    /**
     * Returns client instance
     *
     *
     * @param string|Service|Settings|Closure $identifier
     * @return static
     * @throws Service\Exception
     * fallback instead default
     */
    public static function getInstance($identifier = self::PRIMARY)
    {
        $serviceClassName = get_called_class();

        if (is_string($identifier)) {
            $resource = &self::$instances[$serviceClassName][$identifier];

            if (null !== $resource) {
                return $resource;
            }

            $emptyRegistry = !isset(self::$registry[$serviceClassName]);
            if ($emptyRegistry
                || !array_key_exists($identifier, self::$registry[$serviceClassName])) {

                if ($emptyRegistry
                    || !array_key_exists(self::FALLBACK, self::$registry[$serviceClassName])) {
                    throw new Service\Exception('Service ' . $serviceClassName . ' not configured for "' . $identifier . '", fallback missing',
                        Service\Exception::NO_FALLBACK);
                }

                $resource = static::getInstance(self::FALLBACK);
                return $resource;
            } else {
                $settings = self::$registry[$serviceClassName][$identifier];

                /**
                 * instance forwarding
                 */
                if (is_string($settings) && isset(self::$registry[$serviceClassName][$settings])) {
                    $newSettings = self::$registry[$serviceClassName][$settings];
                    if ($settings !== $newSettings) {
                        $resource = static::getInstance($settings);
                        return $resource;
                    }
                }

                $resource = new static($settings);
                $resource->settings->identifier = $identifier;
                return $resource;
            }
        }

        if ($identifier instanceof Service) {
            return $identifier;
        }

        if ($identifier instanceof Settings) {
            return new static($identifier);
        }

        throw new Exception('String identifier required', Exception::INVALID_ARGUMENT); // TODO change message here
    }


    /**
     * @var null|Settings
     */
    protected $settings;

    protected $driver;

    /**
     * @return object
     * @throws Service\Exception
     */
    public function getDriver()
    {
        if (null === $this->driver) {
            if ($this->settings && $this->settings->driverClassName) {
                $driverClass = $this->settings->driverClassName;
            } else {
                $scheme = $this->settings->scheme;
                $scheme = explode('.', $scheme, 2);
                if (2 === count($scheme)) {
                    $driverClass = '\\' . Utils::toCamelCase($scheme[0], '-') . '\\'
                        . get_called_class() . '\Driver\\' . Utils::toCamelCase($scheme[1], '-');
                } else {
                    $driverClass = get_called_class() . '\Driver\\' . Utils::toCamelCase($scheme[0], '-');
                }

            }
            if (!class_exists($driverClass)) {
                throw new Service\Exception($driverClass . ' (' . $this->settings->scheme . ') not found', Service\Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($this->settings);
        }

        /*
        if ($this->driver instanceof Migration_Required) {
            Migration_Manager::getInstance()->perform($this->driver->getMigration());
        }
        */

        return $this->driver;
    }

    /**
     * @return string
     */
    protected static function getSettingsClassName()
    {
        return Settings::className();
    }
}