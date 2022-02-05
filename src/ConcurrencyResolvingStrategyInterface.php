<?php

interface ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void;
}