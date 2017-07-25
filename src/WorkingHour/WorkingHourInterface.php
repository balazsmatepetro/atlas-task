<?php

namespace AtlasTask\WorkingHour;

/**
 * Description of WorkingHourInterface
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
interface WorkingHourInterface
{
    /**
     * Returns the hour when the shift ends.
     *
     * @return int
     */
    public function getEndHour();

    /**
     * Returns the shift in hours.
     *
     * @return int
     */
    public function getShiftHours();

    /**
     * Return the hour when the shift starts.
     *
     * @return int
     */
    public function getStartHour();
}
