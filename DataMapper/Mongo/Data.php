<?php
namespace DBWorker\DataMapper\Mongo;

/**
 * @method static \DBWorker\DataMapper\Mongo\Mapper getMapper()
 */
class Data extends \DBWorker\DataMapper\Data
{
    protected static $mapper = '\DBWorker\DataMapper\Mongo\Mapper';

    public static function query($expr)
    {
        return static::getMapper()->query($expr);
    }

    public static function iterator($expr = null)
    {
        return static::getMapper()->iterator($expr);
    }
}
