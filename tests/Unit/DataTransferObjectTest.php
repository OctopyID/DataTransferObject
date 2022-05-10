<?php

namespace Octopy\DTO\Tests\Unit;

use ArrayIterator;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use JsonSerializable;
use Octopy\DTO\DataTransferObject;
use Octopy\DTO\Tests\TestCase;

class DataTransferObjectTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanConvertToArray() : void
    {
        $dto = new DataTransferObject(collect([
            'name' => 'Foo Bar',
        ]));

        $this->assertIsArray($dto->toArray());
        $this->assertArrayHasKey('name', $dto->toArray());
    }

    /**
     * @return void
     */
    public function testItCanConvertToJson() : void
    {
        $dto = new DataTransferObject(collect([
            'name' => 'Foo Bar',
        ]));

        $this->assertIsString($dto->toJson());
        $this->assertEquals('{"name":"Foo Bar"}', $dto->toJson());
    }

    /**
     * @return void
     */
    public function testItCanTransformFromArrayable() : void
    {
        $dto = new DataTransferObject(new Request([
            'name' => 'Foo Bar',
        ]));

        $this->assertArrayHasKey('name', $dto->toArray());
    }

    /**
     * @return void
     */
    public function testItCanTransformFromJsonable() : void
    {
        $dto = new DataTransferObject(new class implements Jsonable
        {
            public function toJson($options = 0) : string
            {
                return '{"name":"Foo Bar"}';
            }
        });

        $this->assertArrayHasKey('name', $dto->toArray());
    }

    /**
     * @return void
     */
    public function testItCanTransformFromJsonString() : void
    {
        $dto = new DataTransferObject('{"name":"Foo Bar"}');

        $this->assertArrayHasKey('name', $dto->toArray());
    }

    /**
     * @return void
     */
    public function testItCanTransformFromJsonSerializable() : void
    {
        $dto = new DataTransferObject(new class implements JsonSerializable
        {
            public function jsonSerialize() : array
            {
                return ['name' => 'Foo Bar'];
            }
        });

        $this->assertArrayHasKey('name', $dto->toArray());
    }

    /**
     * @return void
     */
    public function testItCanTransformFromTraversable() : void
    {
        $dto = new DataTransferObject(new ArrayIterator([
            'name' => 'Foo Bar',
        ]));

        $this->assertArrayHasKey('name', $dto->toArray());
    }
}
