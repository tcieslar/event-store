<?php

abstract class Repository
{

    public function __construct(
        private UnitOfWork $unitOfWork
    )
    {
    }

    protected function addAggregate(Aggregate $aggregate): void
    {
        $this->unitOfWork->persist($aggregate);
    }
}