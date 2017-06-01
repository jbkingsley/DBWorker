<?php

namespace DBWorker\DataMapper\Type;

class Json extends Complex
{
    public function normalize($value, array $attribute)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($this->isNull($value)) {
            return [];
        }

        return \DBWorker\safe_json_decode($value, true);
    }

    public function store($value, array $attribute)
    {
        $value = parent::store($value, $attribute);

        if ($this->isNull($value)) {
            return;
        }

        return \DBWorker\safe_json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function restore($value, array $attribute)
    {
        if ($this->isNull($value)) {
            return [];
        }

        return $this->normalize($value, $attribute);
    }
}
