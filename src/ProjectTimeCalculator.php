<?php

namespace AtlasTask;

use AtlasTask\CalculationException;
use AtlasTask\WorkingDayValidator\WorkingDayValidatorInterface;
use AtlasTask\WorkingHour\WorkingHourInterface;
use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Description of ProjectTimeCalculator
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
class ProjectTimeCalculator
{
    /**
     * A day in seconds.
     * 
     * @var int
     */
    const DAY_IN_SECONDS = 86400;

    /**
     * An hour in seconds.
     * 
     * @var int
     */
    const HOUR_IN_SECONDS = 3600;

    /**
     * The working day validator.
     *
     * @var WorkingDayValidatorInterface
     */
    private $workingDayValidator;

    /**
     * The working hours.
     *
     * @var WorkingHourInterface
     */
    private $workingHour;

    /**
     * Creates a new ProjectTimeCalculator object.
     *
     * @param WorkingHourInterface $workingHour The working hours object.
     * @param WorkingDayValidatorInterface $workingDayValidator The working day validator object.
     */
    public function __construct(WorkingHourInterface $workingHour, WorkingDayValidatorInterface $workingDayValidator)
    {
        $this->workingHour = $workingHour;
        $this->workingDayValidator = $workingDayValidator;
    }

    /**
     * Calculates the end date of project by the start date and the planned hours.
     *
     * @param DateTimeInterface $startDate The start date.
     * @param int $plannedHours The planned hours.
     * @return DateTime
     */
    public function calculate(DateTimeInterface $startDate, $plannedHours)
    {
        // If the planned hours parameter is not an integer or less than or equal to zero we have to throw an exception.
        if (!is_int($plannedHours) || 0 >= $plannedHours) {
            throw new InvalidArgumentException('The planned hours must be an integer and greater than zero!');
        }
        // When the given start date is not a working day we have to throw an exception.
        if (!$this->workingDayValidator->isWorkingDay($startDate)) {
            throw new CalculationException('The start date is a non-working day!');
        }
        // When the given start time is not in working hours we have to throw an exception.
        if (!$this->isInWorkingHours($startDate)) {
            throw new CalculationException('The start hour is out of working hours!');
        }
        // Creating the end date of the project. We use the start date object for this, doesn't matter 
        // the given start date object is immutable (DateTimeImmutable) or not.
        $endDate = new DateTime($startDate->format('Y-m-d H:i:s'));
        // The planned working hours in seconds.
        $plannedSeconds = $plannedHours * self::HOUR_IN_SECONDS;
        // At first we have to calculate the difference between the shift end date and project start date
        // regardless it's only a second, a whole shift or more then a shift.
        $difference = $this->createShiftEndDate($startDate)->getTimestamp() - $startDate->getTimestamp();
        // It's on the same day, we only have to add the planned seconds.
        if ($plannedSeconds < $difference) {
            $endDate->add($this->createSecondsInterval($plannedSeconds));
        } else {
            // We have to add the difference to the end date.
            $endDate->add($this->createSecondsInterval($difference));
            // We have to extract the difference from the planned seconds.
            $plannedSeconds -= $difference;
            // If the planned seconds is greater than zero, we have to calculate the and date by shifts.
            if (0 < $plannedSeconds) {
                $this->calculateByShifts($endDate, $plannedSeconds);
            }
        }
        // Returns the DateTime object.
        return $endDate;
    }

    /**
     * Calculates the project end date by shifts.
     *
     * @param DateTime $endDate The date which we will be used during the calculation.
     * @param int $plannedSeconds The planned seconds.
     * @return void
     */
    private function calculateByShifts(DateTime $endDate, $plannedSeconds)
    {
        // If the planned seconds less than or equal to zero we have to throw an exception, because
        // in this case we shouldn't calculate the end date by shifts.
        if (0 >= $plannedSeconds) {
            throw new InvalidArgumentException('The planned seconds must be greater than zero!');
        }
        // The shift hours in seconds.
        $shiftSeconds = $this->workingHour->getShiftHours() * self::HOUR_IN_SECONDS;
        // The day difference interval.
        $dayDiffInterval = $this->createSecondsInterval(self::DAY_IN_SECONDS - $shiftSeconds);
        // We have to add the day difference interval to the end date (it will be the next day).
        $endDate->add($dayDiffInterval);
        // Starting calculation.
        while (0 < $plannedSeconds) {
            // If the given date is not a working day...
            if (!$this->workingDayValidator->isWorkingDay($endDate)) {
                // ...we have to increase the and date by a whole day...
                $endDate->add($this->createSecondsInterval(self::DAY_IN_SECONDS));
                // ...and continue the iteration without decreasing the planned seconds.
                continue;
            }
            // If the planned seconds less than the length of shift...
            if ($plannedSeconds < $shiftSeconds) {
                // ...we just add the remaining time to the and date.
                $endDate->add($this->createSecondsInterval($plannedSeconds));
                $plannedSeconds = 0;
                // We must break the iteration!
                break;
            }
            // Decreasing the planned seconds by the length of shift.
            $plannedSeconds -= $shiftSeconds;
            // The developer was working a whole shift on the project, so we have to add this amount of
            // time to the end date.
            $endDate->add($this->createSecondsInterval($shiftSeconds));
            // If we still have planned seconds that means the project hasn't finished yet, so we have
            // to continue it...
            if (0 < $plannedSeconds) {
                // ...on the next day.
                $endDate->add($dayDiffInterval);
            }
        }
    }

    /**
     * Returns a DateInterval object by the given seconds.
     *
     * @param int $seconds The given seconds.
     * @return DateInterval
     */
    private function createSecondsInterval($seconds)
    {
        return (new DateInterval('PT' . $seconds . 'S'));
    }

    /**
     * Returns the shift end date by the given date.
     *
     * @param DateTimeInterface $from The date by the shift end date is calculated.
     * @return DateTime
     */
    private function createShiftEndDate(DateTimeInterface $from)
    {
        return DateTime::createFromFormat('Y-m-d G', $from->format('Y-m-d') . ' ' . $this->workingHour->getEndHour());
    }

    /**
     * Returns true when the given time is in working hours, else false.
     *
     * @param DateTimeInterface $time The checked time.
     * @return boolean
     */
    public function isInWorkingHours(DateTimeInterface $time)
    {
        // Creating an immutable start date object by the given date and the shift start hour.
        $start = DateTimeImmutable::createFromFormat(
            'Y-m-d G',
            $time->format('Y-m-d') . ' ' . $this->workingHour->getStartHour()
        );
        // Creating an immutable end date object by the shift start date and the shift hours.
        $end = $start->add(new DateInterval('PT' . $this->workingHour->getShiftHours() . 'H'));
        // Retrieving timestamp of the given DateTime object.
        $timestamp = $time->getTimestamp();
        // Comparing timestamps.
        return $timestamp >= $start->getTimestamp() && $timestamp <= $end->getTimestamp();
    }
}
