<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use JetBrains\PhpStorm\Pure;

class AggregateType
{
    public function __construct(
        public readonly string $classFqcn
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
}