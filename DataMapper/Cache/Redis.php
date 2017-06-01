<?php
declare(strict_types=1);

namespace DBWorker\DataMapper\Cache;

trait Redis
{
    use Hooks;

    protected function getCache(array $id): array
    {
        $key = $this->getCacheKey($id);
        $redis = $this->getCacheService($key);

        try {
            if ($record = $redis->get($key)) {
                $record = \DBWorker\safe_json_decode($record, true);
            }

            return $record ?: [];
        } catch (\UnexpectedValueException $exception) {
            if (DEBUG) {
                throw $exception;
            }

            return [];
        }
    }

    protected function deleteCache(array $id): bool
    {
        $key = $this->getCacheKey($id);
        $redis = $this->getCacheService($key);

        return $redis->delete($key);
    }

    protected function saveCache(array $id, array $record, $ttl = null): bool
    {
        $key = $this->getCacheKey($id);
        $redis = $this->getCacheService($key);
        $ttl = $ttl ?: $this->getCacheTTL();

        return $redis->setex($key, $ttl, \DBWorker\safe_json_encode($record, JSON_UNESCAPED_UNICODE));
    }
}
