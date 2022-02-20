<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use JetBrains\PhpStorm\Pure;

class AggregateType
{
    public function __construct(
        private string $classFqcn
    )
    {
    }

    #[Pure] public static function byAggregate(AggregateInterface $aggregate): self
    {
        return new self(get_class($aggregate));
    }

    public function getTypeName(): string
    {
        $array = explode('\\', $this->classFqcn);
        return $array[count($array) - 1];
    }

    public function toString(): string
    {
        return $this->classFqcn;
    }
}