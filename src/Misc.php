<?php

namespace Yaoi;
class Misc
{

    public static function &arrayMergeRecursiveDistinct(array &$array1, &$array2 = null)
    {
        $merged = $array1;
        $key = $val = null;

        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key]))
                    $merged[$key] = isset($merged[$key]) && is_array($merged[$key])
                        ? self::arrayMergeRecursiveDistinct($merged[$key], $array2[$key])
                        : $array2[$key];
            }
        } else {
            $merged[$key] = $val;
        }

        return $merged;
    }
}