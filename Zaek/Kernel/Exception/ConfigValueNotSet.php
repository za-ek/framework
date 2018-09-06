<?php
namespace Zaek\Kernel\Exception;

class ConfigValueNotSet extends \DomainException
{
    /**
     * @param $section
     * @param $option
     * @return ConfigValueNotSet
     */
    public static function create($section, $option)
    {
        return new self('Config value not set: "' . $section . ':'.$option.'"');
    }
}