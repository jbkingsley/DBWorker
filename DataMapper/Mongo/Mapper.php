<?php
namespace DBWorker\DataMapper\Mongo;

use DBWorker\DataMapper\Data;
use DBWorker\Service;

class Mapper extends \DBWorker\DataMapper\Mapper
{
    public function query($expr)
    {
        $result = [];

        foreach (static::iterator($expr) as $data) {
            $result[$data->id()] = $data;
        }

        return $result;
    }

    public function iterator($expr = null)
    {
        if ($expr instanceof \MongoCursor) {
            $cursor = $expr;
        } elseif ($expr === null || is_array($expr)) {
            $cursor = $this->getService()
                           ->getCollection($this->getCollection())
                           ->find($expr ?: []);
        } else {
            throw new \InvalidArgumentException('Invalid mongo query expressions');
        }

        foreach ($cursor as $record) {
            $data = $this->pack($record);

            yield $data;
        }
    }

    public function pack(array $record, Data $data = null): Data
    {
        if (isset($record['_id'])) {
            $record['_id'] = (string) $record['_id'];
        }

        return parent::pack($record, $data);
    }

    public function unpack(Data $data, array $options = null): array
    {
        $record = parent::unpack($data, $options);

        if ($data->isFresh()) {
            $record = \DBWorker\array_trim($record);
        }

        return $record;
    }

    protected function doFind(array $id, Service $service = null, string $collection = null): array
    {
        $service = $service ?: $this->getService();
        $collection = $collection ?: $this->getCollection();

        return $service->findOne($collection, ['_id' => $this->normalizeIDValue($id)]);
    }

    protected function doInsert(Data $data, Service $service = null, string $collection = null): array
    {
        $service = $service ?: $this->getService();
        $collection = $collection ?: $this->getCollection();

        $record = $this->unpack($data);
        $record['_id'] = $this->normalizeIDValue($data->id());

        $service->insert($collection, $record);

        return [
            '_id' => $record['_id'],
        ];
    }

    protected function doUpdate(Data $data, Service $service = null, string $collection = null): bool
    {
        $service = $service ?: $this->getService();
        $collection = $collection ?: $this->getCollection();
        $record = $this->unpack($data, ['dirty' => true]);

        $new = ['$set' => [], '$unset' => []];
        foreach ($record as $key => $value) {
            if ($value === null) {
                $new['$unset'][$key] = '';
            } else {
                $new['$set'][$key] = $value;
            }
        }

        if (!$new['$set']) {
            unset($new['$set']);
        }

        if (!$new['$unset']) {
            unset($new['$unset']);
        }

        return $service->update($collection, ['_id' => $this->normalizeIDValue($data)], $new);
    }

    protected function doDelete(Data $data, Service $service = null, string $collection = null): bool
    {
        $service = $service ?: $this->getService();
        $collection = $collection ?: $this->getCollection();

        return $service->remove($collection, ['_id' => $this->normalizeIDValue($data)]);
    }

    protected function normalizeOptions(array $options): array
    {
        $options = parent::normalizeOptions($options);

        if (count($options['primary_key']) !== 1 || $options['primary_key'][0] !== '_id') {
            throw new \RuntimeException("Mongo data's primary key must be \"_id\"");
        }

        $options['attributes']['_id']['auto_generate'] = true;

        return $options;
    }

    protected function normalizeIDValue($data)
    {
        $id = $data instanceof \DBWorker\DataMapper\Data
            ? $data->id()
            : $data;

        return $id instanceof \MongoId
             ? $id
             : new \MongoId($id);
    }
}
