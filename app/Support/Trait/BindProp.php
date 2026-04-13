<?php

namespace App\Support\Trait;


trait BindProp
{
    /**
     * @var array
     */
    protected array $_prop = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->bindToProp($data);
    }

    public function bindToProp(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $key => $val) {

            if (!property_exists($this, $key)) {
                continue;
            }

            if (str_starts_with($key, "_")) {
                continue;
            }

            $this->{$key} = $val;
            $this->_prop[$key] = $val;
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (!empty($this->_prop)) {
            return $this->_prop;
        }

        foreach ($this as $key => $val) {
            if (str_starts_with($key, "_")) {
                continue;
            }
            $this->_prop[$key] = $val;
        }

        return $this->_prop;
    }


    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @param string|int $key
     * @param null       $default
     * @return mixed|null
     */
    public function getValue(string|int $key, $default = null): mixed
    {
        return get_value($this->toArray(), $key, $default);
        //return isset($this->toArray()[$key]) ? $this->toArray()[$key] : $default;
    }
}
