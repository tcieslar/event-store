<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

class AggregateType
{
    public function __construct(
        private string $classFqcn
    )
    {
    }

    public static function byAggregate(Aggregate $aggregate): self
    {
        return new self(get_class($aggregate));
    }

    public function getTypeName(): string
    {
        $array = explode('\\', $this->classFqcn);
        return $array[count($array) - 1];
    }


    /**
     * @return class-string
     */
    public function toString(): string
    {
        return $this->classFqcn;
    }
}