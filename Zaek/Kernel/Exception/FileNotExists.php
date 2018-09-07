<?php
namespace Zaek\Kernel\Exception;

class FileNotExists extends \RuntimeException
{
    /**
     * @param $file
     * @return FileNotExists
     */
    public static function create($file)
    {
        return new self("File <{$file}> does not exist");
    }
}