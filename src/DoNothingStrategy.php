<?php

class DoNothingStrategy implements ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void
    {
        return ;
    }
}