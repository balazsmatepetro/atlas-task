<?php

namespace AtlasTask\WorkingDayValidator;

use DateTimeInterface;

/**
 * Description of AtlasSoftWorkingDayValidator
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
class AtlasSoftWorkingDayValidator implements WorkingDayValidatorInterface
{
    /**
     * Returns true if the given date is a weekday, else false.
     *
     * @param DateTimeInterface $date The validated date. 
     * @return boolean
     */
    public function isWorkingDay(DateTimeInterface $date)
    {
        return 6 > (int)$date->format('N');
    }
}
