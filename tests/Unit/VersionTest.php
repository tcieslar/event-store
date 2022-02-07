<?php

namespace Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Aggregate\Version;

class VersionTest extends TestCase
{
    public function testInvalidVersionNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $version = Version::createVersion(-100);
    }

    public function testIsHigherThen(): void
    {
        $versionA = Version::createVersion(123);
        $versionB = Version::createVersion(456);

        $this->assertTrue($versionB->isHigherThen($versionA));
    }

}