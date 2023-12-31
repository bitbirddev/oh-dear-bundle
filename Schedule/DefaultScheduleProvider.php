<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Schedule;

use bitbirddev\OhDearBundle\Message\HeartbeatMessageAsync;
use bitbirddev\OhDearBundle\Message\HeartbeatMessageSync;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
final class DefaultScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        if (class_exists('App\Schedule\DefaultScheduleProvider')) {
            $schedule = (new \App\Schedule\DefaultScheduleProvider())->getSchedule();
        } else {
            $schedule = new Schedule();
        }

        return $schedule->add(
            RecurringMessage::every('4 minute', new HeartbeatMessageSync()),
            RecurringMessage::every('4 minute', new HeartbeatMessageAsync()),
        );
    }
}
