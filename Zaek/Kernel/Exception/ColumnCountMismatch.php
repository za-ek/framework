<?php
namespace Zaek\Kernel\Exception;

class ColumnCountMismatch extends \Exception
{
    /**
     * @param $expected
     * @param $real
     * @return ColumnCountMismatch
     */
    public static function create($expected, $real)
    {
        return new self('Column count mismatch. Expected: ' . $expected .'. Real: '.$real);
    }
}