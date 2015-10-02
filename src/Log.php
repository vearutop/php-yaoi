<?php
namespace Yaoi;

use Yaoi\Log\Driver;
use Yaoi\Log\Settings;
use Yaoi\Service;

/**
 * Class Log
 * @method Log\Driver getDriver()
 */
class Log extends Service
{
    const TYPE_MESSAGE = 'm';
    const TYPE_ERROR = 'e';
    const TYPE_SUCCESS = 's';

    /** @var  Settings */
    protected $settings;

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        if ($this->settings->castToString) {
            $this->getDriver()->push((string)$message, $type);
        }
        else {
            $this->getDriver()->push($message, $type);
        }
        return $this;
    }

    protected static function getSettingsClassName()
    {
        return Settings::className();
    }


    private static $void;
    public static function void() {
        if (null === self::$void) {
            self::$void = new Log('void');
        }
        return self::$void;
    }

}