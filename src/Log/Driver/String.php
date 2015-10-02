<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 02.10.2015
 * Time: 20:24
 */

namespace Yaoi\Log\Driver;


use Yaoi\BaseClass;
use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Settings;

class String extends BaseClass implements Driver
{
    private $dsn;

    public function __construct(Settings $dsn = null)
    {
        $this->dsn = $dsn;
    }


    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        $this->dsn->storage .= $message . "\n";
    }

}