<?php

namespace YaoiTests\Helper\Http;


class ClientHelper
{
    public static function parseRequest($requestString) {
        $lines = explode("\n",$requestString);

        $result = array();
        $result['Head'] = trim($lines[0]);

        $result['Head'] = str_replace('HTTP/1.1', 'HTTP/1.0', $result['Head']); // HHVM uses 1.1, PHP 5 uses 1.0
        $result['Body'] = null;

        unset($lines[0]);
        $empty = false;
        foreach ($lines as $rline) {
            $line = trim($rline);
            if (!$line) {
                $empty = true;
            }

            if (!$empty) {
                if (strpos($line, ':')) {
                    list($header, $value) = explode(':', $line, 2);
                    $result[$header] = trim($value);
                }
                else {
                    $result []= $line;
                }
            } else {
                if ((null === $result['Body']) && !$line) {
                    $result['Body'] = '';
                }
                else {
                    $result ['Body'] .= $rline;
                }
            }

        }
        return $result;
    }

}