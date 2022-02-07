<?php

namespace EventPublisher;

use DateTimeImmutable;
use Event\EventCollection;
use JsonException;

class FileEventPublisher implements EventPublisherInterface
{
    /**
     * @throws JsonException
     */
    public function publish(EventCollection $collection): void
    {
        foreach ($collection as $event) {
            $fileName = __DIR__ . '/../../event_log.txt';
            $content = file_get_contents($fileName);
            $content .= sprintf("\n\n[%s] \n%s", (new DateTimeImmutable())->format('d-m-Y H:i:s'),
                json_encode($event, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
            //file_put_contents($fileName, $content);
        }
    }
}