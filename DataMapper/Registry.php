<?php
declare(strict_types=1);

namespace DBWorker\DataMapper;

class Registry
{
    use \DBWorker\Traits\Singleton;

    /**
     * 是否开启DataMapper的Data注册表功能.
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * 缓存的Data实例.
     *
     * @var array
     */
    private $members = [];

    /**
     * 开启缓存.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * 关闭缓存.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * 缓存是否开启.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 把Data实例缓存起来.
     *
     * @param Data $data
     */
    public function set(Data $data)
    {
        $class = get_class($data);
        if (!$this->isEnabled()) {
            return false;
        }

        if ($data->isFresh()) {
            return false;
        }

        if (!$id = $data->id(true)) {
            return false;
        }

        $key = self::key($class, $id);
        $this->members[$key] = $data;
    }

    /**
     * 根据类名和主键值，获得缓存结果.
     *
     * @param string $class
     * @param array  $id
     *
     * @return Data|false
     */
    public function get(string $class, array $id)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $key = self::key($class, $id);

        return $this->members[$key] ?? false;
    }

    /**
     * 删除缓存结果.
     *
     * @param string $class
     * @param array  $id
     */
    public function remove(string $class, array $id)
    {
        if ($this->isEnabled()) {
            $key = self::key($class, $id);
            unset($this->members[$key]);
        }
    }

    /**
     * 把所有的缓存都删除掉.
     */
    public function clear()
    {
        $this->members = [];
    }

    /**
     * 生成缓存数组的key.
     *
     * @param string $class
     * @param mixed  $id
     *
     * @return string
     */
    private static function key(string $class, array $id): string
    {
        $class = strtolower(ltrim($class, '\\'));
        ksort($id);

        $key = '';
        foreach ($id as $prop => $val) {
            if ($key) {
                $key .= ';';
            }
            $key .= "{$prop}:{$val}";
        }

        return $class . '@' . $key;
    }
}
