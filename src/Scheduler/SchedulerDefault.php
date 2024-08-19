<?php

namespace App\Scheduler;

use App\Scheduler\Handler\Historiser;
use App\Service\HistoriserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
final class SchedulerDefault implements ScheduleProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {
    }

    public function getSchedule(): Schedule
    {

        $schedule=new Schedule();

        return $schedule;

    }
}



