<?php
namespace Sparrow;

class Event {
    /**
     * @param string $event
     * @return string|false
     */
    public static function getMethod(string $event)
    {
        $events = file_get_contents(Directory::rootPath() . EVENTS_PATH);
        if (!$events)
            return false;
        return json_decode($events, 1)[$event];
    }

    public static function listen(string $event, ...$params)
    {
        $listeners = self::getMethod($event);
        foreach ($listeners as $listener)
            $listener['class_name']::{$listener['method']}(...$params ?? null);
    }
}