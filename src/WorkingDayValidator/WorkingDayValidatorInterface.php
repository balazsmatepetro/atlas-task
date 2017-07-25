<?php

namespace AtlasTask\WorkingDayValidator;

use DateTimeInterface;

/**
 * Description of WorkingDayValidatorInterface
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
interface WorkingDayValidatorInterface
{
    /**
     * Returns true if the given date matches the conditions, else false.
     *
     * @param DateTimeInterface $date The validated date.
     * @return boolean
     */
    public function isWorkingDay(DateTimeInterface $date);
}
