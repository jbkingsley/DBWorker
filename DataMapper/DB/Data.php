<?php
namespace DBWorker\DataMapper\DB;

use DBWorker\Service;
use DBWorker\Service\DB\Select;

/**
 * @method static \DBWorker\DataMapper\DB\Mapper getMapper()
 */
class Data extends \DBWorker\DataMapper\Data
{
    protected static $mapper = '\DBWorker\DataMapper\DB\Mapper';

    public static function select(): Select
    {
        return static::getMapper()->select();
    }

    public static function getBySQL(string $sql, array $parameters = [], Service $service = null): array
    {
        $result = [];

        foreach (static::getBySQLAsIterator($sql, $parameters, $service) as $data) {
            $id = $data->id();

            if (is_array($id)) {
                $result[] = $data;
            } else {
                $result[$id] = $data;
            }
        }

        return $result;
    }

    public static function getBySQLAsIterator(string $sql, array $parameters = [], Service $service = null): \Generator
    {
        return static::getMapper()->getBySQLAsIterator($sql, $parameters, $service);
    }
}
