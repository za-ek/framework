<?php
namespace Zaek\Kernel\Exception;

class CouldNotCreateFile extends \RuntimeException
{
    /**
     * @param $file
     * @return CouldNotCreateFile
     */
    public static function create($file)
    {
        return new self("Could not create file <{$file}>");
    }
}