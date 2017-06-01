<?php
declare(strict_types=1);

namespace DBWorker\DataMapper\DB;

class Select extends \DBWorker\Service\DB\Select
{
    public function get(int $limit = 0)
    {
        $result = [];

        foreach (parent::get($limit) as $data) {
            $result[$data->id()] = $data;
        }

        return $result;
    }
}
