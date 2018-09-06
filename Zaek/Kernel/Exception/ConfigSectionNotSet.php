<?php
namespace Zaek\Kernel\Exception;

class ConfigSectionNotSet extends \DomainException
{
    /**
     * @param $section
     * @return ConfigSectionNotSet
     */
    public static function create($section)
    {
        return new self('Config section not set: "' . $section . '"');
    }
}