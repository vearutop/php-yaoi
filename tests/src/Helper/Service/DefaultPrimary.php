<?php

namespace YaoiTests\Helper\Service;


use Yaoi\Service;
use YaoiTests\Helper\Service\BasicExposed;

class DefaultPrimary extends BasicExposed
{

}
DefaultPrimary::register(null, Service::PRIMARY);