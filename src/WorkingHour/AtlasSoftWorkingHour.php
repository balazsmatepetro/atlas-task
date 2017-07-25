<?php

namespace AtlasTask\WorkingHour;

/**
 * Description of AtlasSoftWorkingHour
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
class AtlasSoftWorkingHour implements WorkingHourInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEndHour()
    {
        return 17;
    }

    /**
     * {@inheritDoc}
     */
    public function getShiftHours()
    {
        return $this->getEndHour() - $this->getStartHour();
    }

    /**
     * {@inheritDoc}
     */
    public function getStartHour()
    {
        return 9;
    }
}
