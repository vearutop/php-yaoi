<?php

abstract class Database_Abstract_Exception extends Exception {
    const DEFAULT_NOT_SET = 1;
    const CONNECTION_ERROR = 2;
    const WRONG_SERVER_TYPE = 3;
    const NO_DRIVER = 4;
    const QUERY_ERROR = 5;
}