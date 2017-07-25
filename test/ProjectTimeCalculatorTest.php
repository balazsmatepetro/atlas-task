<?php

namespace AtlasTask\Test;

use AtlasTask\ProjectTimeCalculator;
use AtlasTask\WorkingDayValidator\WorkingDayValidatorInterface;
use AtlasTask\WorkingHour\WorkingHourInterface;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Description of ProjectTimeCalculatorTest
 * 
 * @author Balázs Máté Petró <petrobalazsmate@gmail.com>
 */
class ProjectTimeCalculatorTest extends TestCase
{
    /**
     * Tests the 'calculate()' method throws an InvalidArgumentException when the given planned hours is invalid.
     *
     * @dataProvider providerInvalidPlannedHours
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The planned hours must be an integer and greater than zero!
     * @param mixed $plannedHours The given value.
     * @return void
     */
    public function testCalculateThrowsAnInvalidArgumentExceptionWhenThePlannedHourIsInvalid($plannedHours)
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingHours = Mockery::mock(WorkingHourInterface::class);

        (new ProjectTimeCalculator($workingHours, $workingDayValidator))->calculate((new DateTime), $plannedHours);
    }

    /**
     * Tests the 'calculate()' method throws a CalculationException when the given start date is a non-working day.
     *
     * @expectedException AtlasTask\CalculationException
     * @expectedExceptionMessage The start date is a non-working day!
     * @return void
     */
    public function testCalculateThrowsAnExceptionWhenTheGivenStartDateIsNotWorkingDay()
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->andReturn(false);

        $workingHours = Mockery::mock(WorkingHourInterface::class);

        (new ProjectTimeCalculator($workingHours, $workingDayValidator))->calculate((new DateTime), 1);
    }

    /**
     * 
     * @dataProvider providerOutOfOfficeHours
     * @expectedException AtlasTask\CalculationException
     * @expectedExceptionMessage The start hour is out of working hours!
     * @return void
     */
    public function testCalculateThrowsAnExceptionWhenTheGivenStartTimeIsAnOutOfOfficeHour($time)
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getShiftHours')->once()->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->once()->andReturn(9);

        (new ProjectTimeCalculator($workingHours, $workingDayValidator))->calculate($time, 1);
    }

    /**
     * Tests the 'calculate()' method returns a DateTime object when the project end date can be calculated.
     *
     * @return void
     */
    public function testCalculateReturnsDateTimeObjectWhenTheTimeCanBeCalculated()
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getEndHour')->once()->andReturn(17);
        $workingHours->shouldReceive('getShiftHours')->once()->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->once()->andReturn(9);

        $calculator = new ProjectTimeCalculator($workingHours, $workingDayValidator);

        $startDate = new DateTime('2017-07-24 09:00:00');

        $this->assertInstanceOf(DateTime::class, $calculator->calculate($startDate, 1));
    }

    /**
     * Tests the 'calculate()' method returns a DateTime object when the project end date can be calculated
     * by an immutable object.
     *
     * @return void
     */
    public function testCalculateCanDoTheCalculationByAnImmutableObject()
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getEndHour')->once()->andReturn(17);
        $workingHours->shouldReceive('getShiftHours')->once()->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->once()->andReturn(9);

        $calculator = new ProjectTimeCalculator($workingHours, $workingDayValidator);

        $startDate = new DateTimeImmutable('2017-07-24 09:00:00');

        $this->assertInstanceOf(DateTime::class, $calculator->calculate($startDate, 1));
    }

    /**
     * Tests the 'calculate()' method returns a DateTime object which has the same date as the start date
     * when the project can be finished within one shift.
     *
     * @return void
     */
    public function testCalculateReturnsTheSameDateWhenTheProjectCanBeFinishedWithinOneShift()
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getEndHour')->andReturn(17);
        $workingHours->shouldReceive('getShiftHours')->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->andReturn(9);

        $calculator = new ProjectTimeCalculator($workingHours, $workingDayValidator);

        $startDate = new DateTime('2017-07-24 09:00:00');

        $this->assertEquals('2017-07-24 10:00:00', $calculator->calculate($startDate, 1)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 11:00:00', $calculator->calculate($startDate, 2)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 12:00:00', $calculator->calculate($startDate, 3)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 13:00:00', $calculator->calculate($startDate, 4)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 14:00:00', $calculator->calculate($startDate, 5)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 15:00:00', $calculator->calculate($startDate, 6)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 16:00:00', $calculator->calculate($startDate, 7)->format('Y-m-d H:i:s'));
        $this->assertEquals('2017-07-24 17:00:00', $calculator->calculate($startDate, 8)->format('Y-m-d H:i:s'));
    }

    /**
     * Tests the 'calculate()' method returns a DateTime object which has not the same date as the start date
     * when the project cannot be finished within one shift.
     *
     * @return void
     */
    public function testCalculateDoesNotReturnTheSameDateWhenTheProjectCannotBeFinishedWithinOneShift()
    {
        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->twice()->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getEndHour')->once()->andReturn(17);
        $workingHours->shouldReceive('getShiftHours')->twice()->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->once()->andReturn(9);

        $calculator = new ProjectTimeCalculator($workingHours, $workingDayValidator);

        $startDate = new DateTime('2017-07-24 09:00:00');

        $this->assertEquals('2017-07-25 11:00:00', $calculator->calculate($startDate, 10)->format('Y-m-d H:i:s'));
    }

    /**
     * Tests the 'calculate()' method returns a DateTime object which has not the same date as the start date
     * when the project cannot be finished within one shift and the next day is a non working day.
     *
     * @return void
     */
    public function testCalculateAddsPlusDayWhenTheNextDayIsNonWorkingDayAndProejctCannotBeFinishedWithinOneShift()
    {
        $day1 = new DateTime('2017-07-25 09:00:00');
        $day2 = new DateTime('2017-07-26 09:00:00');
        $startDate = new DateTime('2017-07-24 09:00:00');

        $workingDayValidator = Mockery::mock(WorkingDayValidatorInterface::class);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->with(equalTo($startDate))->andReturn(true);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->with(equalTo($day1))->andReturn(false);
        $workingDayValidator->shouldReceive('isWorkingDay')->once()->with(equalTo($day2))->andReturn(true);

        $workingHours = Mockery::mock(WorkingHourInterface::class);
        $workingHours->shouldReceive('getEndHour')->once()->andReturn(17);
        $workingHours->shouldReceive('getShiftHours')->twice()->andReturn(8);
        $workingHours->shouldReceive('getStartHour')->once()->andReturn(9);

        $calculator = new ProjectTimeCalculator($workingHours, $workingDayValidator);

        $this->assertEquals('2017-07-26 11:00:00', $calculator->calculate($startDate, 10)->format('Y-m-d H:i:s'));
    }

    /**
     * Returns an array of invalid planned hours.
     *
     * @return array
     */
    public function providerInvalidPlannedHours()
    {
        return [
            ['0'],
            [0],
            ['string'],
            [3.14],
            [true],
            [[]],
            [new stdClass],
            [function () {
                return 1;
            }]
        ];
    }

    /**
     * Returns a collection of out of office DateTime objects.
     *
     * @return DateTime[]
     */
    public function providerOutOfOfficeHours()
    {
        return [
            [new DateTime('2017-07-24 17:01:00')],
            // ---
            [new DateTime('2017-07-24 00:00:00')],
            [new DateTime('2017-07-24 01:00:00')],
            [new DateTime('2017-07-24 02:00:00')],
            [new DateTime('2017-07-24 03:00:00')],
            [new DateTime('2017-07-24 04:00:00')],
            [new DateTime('2017-07-24 05:00:00')],
            [new DateTime('2017-07-24 06:00:00')],
            [new DateTime('2017-07-24 07:00:00')],
            [new DateTime('2017-07-24 08:00:00')],
            [new DateTime('2017-07-24 18:00:00')],
            [new DateTime('2017-07-24 19:00:00')],
            [new DateTime('2017-07-24 20:00:00')],
            [new DateTime('2017-07-24 21:00:00')],
            [new DateTime('2017-07-24 22:00:00')],
            [new DateTime('2017-07-24 23:00:00')]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }
}
