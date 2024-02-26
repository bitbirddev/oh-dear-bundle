<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Schedule;

use bitbirddev\OhDearBundle\Message\HeartbeatMessageAsync;
use bitbirddev\OhDearBundle\Message\HeartbeatMessageSync;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('default')]
final class DefaultScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(
        protected CacheInterface $cache
    ) {
    }

    public function getSchedule(): Schedule
    {
        if (class_exists('App\Schedule\DefaultScheduleProvider')) {
            $schedule = (new \App\Schedule\DefaultScheduleProvider($this->cache))->getSchedule();
        } else {
            $schedule = new Schedule();
        }

        return $schedule->add(
            // has to be lower than the max execution time of bin/console messenger:consume
            RecurringMessage::every('3 minutes', new HeartbeatMessageSync()),
            RecurringMessage::every('3 minutes', new HeartbeatMessageAsync()),
        )->stateful($this->cache);
    }
}
