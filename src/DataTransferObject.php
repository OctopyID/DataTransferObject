<?php

namespace Octopy\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use Traversable;

class DataTransferObject implements Arrayable, Jsonable
{
    /**
     * @var array
     */
    protected array $transformed = [];

    /**
     * @param  mixed $original
     */
    public function __construct(protected mixed $original = [])
    {
        $this->transformed = $this->transform($this->original);
    }

    /**
     * @param  mixed $data
     * @return static
     */
    public static function make(mixed $data) : self
    {
        return new static($data);
    }

    /**
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return Arr::get($this->transformed, $key, $default);
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return Arr::has($this->transformed, $key);
    }

    /**
     * @param  string|array $key
     * @param  mixed        $value
     * @return static
     */
    public function set(string|array $key, mixed $value = null) : static
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->set($name, $value);
            }

            return $this;
        };

        Arr::set($this->transformed, $key, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        if ($this->original instanceof Model) {
            return collect($this->transformed)->filter(function ($value, $key) {
                return ! in_array($key, $this->original->getHidden());
            })
                ->toArray();
        }

        return $this->transformed;
    }

    /**
     * @param  int $options
     * @return bool|string
     */
    public function toJson($options = 0) : bool|string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @param  mixed $original
     * @return array
     */
    private function transform(mixed $original) : array
    {
        if ($original instanceof Arrayable) {
            return $original->toArray();
        }

        if ($original instanceof Jsonable) {
            return json_decode($original->toJson(), true);
        }

        if ($original instanceof JsonSerializable) {
            return $original->jsonSerialize();
        }

        if ($original instanceof Traversable) {
            return iterator_to_array($original);
        }

        if (is_string($original)) {
            try {
                $original = json_decode($original, true);
            } catch (JsonException) {
                throw new InvalidArgumentException('Invalid JSON string');
            }
        }

        return $original;
    }
}
