<?php

namespace Yaoi\Date;
use Yaoi\Mock;
use Yaoi\Mock\Able;
use Yaoi\Service;

/**
 * Class TimeMachine
 *
 * @summary TimeMachine grants mockable and shiftable access to timestamp and date data
 * @package Yaoi\Date
 */
class TimeMachine extends Service implements Able
{
    public function __construct($settings = null)
    {
        parent::__construct($settings);
        $this->mock = Mock::getNull();
    }


    const MOCK_NOW = 'now';
    const MOCK_STR_TO_TIME = 'str_to_time';
    const MOCK_MICRO_NOW = 'micro_now';

    public function now()
    {
        return $this->mock->branch(static::MOCK_NOW)->get(null, function() {
            return time();
        });
    }

    public function microNow()
    {
        return $this->mock->branch(static::MOCK_MICRO_NOW)->get(null, function() {
            return microtime(1);
        });
    }

    public function strToTime($string, $now = null)
    {
        return $this->mock->branch(static::MOCK_STR_TO_TIME, $string)->get(null, function() use ($string, $now) {
            return strtotime($string, $now);
        });
    }


    public function date($format, $timestamp = null)
    {
        if (null === $timestamp) {
            $result = date($format, $this->now());
        } else {
            $result = date($format, $timestamp);
        }
        return $result;
    }


    private $year;
    private $month;
    private $day;
    private static $rusMonths = array(
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'сентября' => '09',
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12',
    );

    public function rusDayMonthToDate($string)
    {
        if (null === $this->year || null === $this->month || null === $this->day) {
            list($this->year, $this->month, $this->day) = explode('/', $this->date('Y/m/d'));
        }

        list($d, $m) = explode(' ', strtr($string, self::$rusMonths));

        /**
         * parsing 12 д. as 2013-12-12
         */
        if (strpos($m, '.') !== false) {
            if ($d < $this->day) {
                $m = $this->month + 1;
                if ($m > 12) {
                    $m = 1;
                }
            } else {
                $m = $this->month;
            }

            if (strlen($m) < 2) {
                $m = '0' . $m;
            }
        }


        if (strlen($d) < 2) {
            $d = '0' . $d;
        }


        if ($m < $this->month) {
            return ($this->year + 1) . '-' . $m . '-' . $d;
        } else {
            return $this->year . '-' . $m . '-' . $d;
        }

    }


    /**
     * @var Mock
     */
    protected $mock;

    public function mock(Mock $dataSet = null)
    {
        if ($dataSet === null) {
            $dataSet = Mock::getNull();
        }
        $this->mock = $dataSet;
        return $this;
    }
}
TimeMachine::register(new Service\Settings(), Service::PRIMARY);