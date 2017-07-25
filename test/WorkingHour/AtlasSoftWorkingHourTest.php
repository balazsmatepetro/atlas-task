<?php

namespace AtlasSoft\Test\WorkingHour;

use AtlasTask\WorkingHour\AtlasSoftWorkingHour;
use PHPUnit_Framework_TestCase;

/**
 * Description of AtlasSoftWorkingHourTest
 * 
 * @author Petró Balázs Máté <petrobalazsmate@gmail.com>
 */
class AtlasSoftWorkingHourTest extends PHPUnit_Framework_TestCase
{
    /**
     * The working hour object.
     *
     * @var AtlasSoftWorkingHour
     */
    private $workingHour;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->workingHour = new AtlasSoftWorkingHour;
    }

    /**
     * Tests the 'getEndHour()' return 17 or not.
     *
     * @return void
     */
    public function testGetEndHourReturnsSeventeen()
    {
        $this->assertEquals(17, $this->workingHour->getEndHour());
    }

    /**
     * Tests the 'getShiftHours()' return 8 or not.
     *
     * @return void
     */
    public function testGetShiftHoursReturnsEight()
    {
        $this->assertEquals(8, $this->workingHour->getShiftHours());
    }

    /**
     * Tests the 'getStartHour()' return 9 or not.
     *
     * @return void
     */
    public function testGetStartHourReturnsNine()
    {
        $this->assertEquals(9, $this->workingHour->getStartHour());
    }
}
