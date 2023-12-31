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
        return (new Schedule())->add(
            RecurringMessage::every('1 minute', new HeartbeatMessageSync()),
            RecurringMessage::every('1 minute', new HeartbeatMessageAsync()),
        );
    }
}
