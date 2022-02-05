<?php

class DoNothingStrategy implements ConcurrencyResolvingStrategyInterface
{
    /**
     * @throws Exception
     */
    public function resolve(ConcurrencyException $exception): never
    {
        throw new Exception('Silence, do nothing.');
    }
}