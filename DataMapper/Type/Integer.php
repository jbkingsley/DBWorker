<?php

namespace DBWorker\DataMapper\Type;

class Integer extends \DBWorker\DataMapper\Type\Number
{
    public function normalize($value, array $attribute)
    {
        return (int) $value;
    }
}
