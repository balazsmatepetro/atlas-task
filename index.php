<?php

require 'vendor/autoload.php';

use AtlasTask\WorkingDayValidator\AtlasSoftWorkingDayValidator;
use AtlasTask\WorkingHour\AtlasSoftWorkingHour;
use AtlasTask\WorkingHourValidator\AtlasSoftWorkingHourValidator;
use AtlasTask\ProjectTimeCalculator;

$date = DateTime::createFromFormat('Y-m-d H:i:s', '2017-07-24 09:00:00');

$calculator = new ProjectTimeCalculator(new AtlasSoftWorkingHour, new AtlasSoftWorkingDayValidator);

echo $calculator->calculate($date, 40)->format('Y-m-d H:i:s'), PHP_EOL;
