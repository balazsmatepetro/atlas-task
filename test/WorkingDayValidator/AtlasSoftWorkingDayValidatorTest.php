<?php

namespace AtlasTask\Test\AtlasSoftWorkingDayValidator;

use AtlasTask\WorkingDayValidator\AtlasSoftWorkingDayValidator;
use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * Description of AtlasSoftWorkingDayValidatorTest
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
class AtlasSoftWorkingDayValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the 'isWorkingDay()' returns true when the date is a weekday.
     *
     * @dataProvider providerWeekdayDates
     * @param DateTime $date The given DateTime object.
     * @return void
     */
    public function testIsWorkingDayReturnsTrueWhenTheGivenDateIsWeekday($date)
    {
        $this->assertTrue((new AtlasSoftWorkingDayValidator)->isWorkingDay($date));
    }

    /**
     * Tests the 'isWorkingDay()' returns false when the date is weekend.
     *
     * @dataProvider providerWeekendDates
     * @param DateTime $date The given DateTime object.
     * @return void
     */
    public function testIsWorkingDayReturnsFalseWhenTheGivenDateIsWeekend($date)
    {
        $this->assertFalse((new AtlasSoftWorkingDayValidator)->isWorkingDay($date));
    }

    /**
     * Returns a collection of weekday dates.
     *
     * @return DateTime[]
     */
    public function providerWeekdayDates()
    {
        return [
            [new DateTime('2017-07-24')],
            [new DateTime('2017-07-25')],
            [new DateTime('2017-07-26')],
            [new DateTime('2017-07-27')],
            [new DateTime('2017-07-28')]
        ];
    }

    /**
     * Returns a collection of weekend dates.
     *
     * @return DateTime[]
     */
    public function providerWeekendDates()
    {
        return [
            [new DateTime('2017-07-29')],
            [new DateTime('2017-07-30')]
        ];
    }
}
