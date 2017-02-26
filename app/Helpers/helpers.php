<?php

/**
 * additional working days in format YYYY-MM-DD
 *
 * @return array
 */
function getWeekendsOn() {
    $url = 'http://standards.openprocurement.org/calendar/weekends-on.json';
    $arDays = json_decode(file_get_contents($url), true);

    return $arDays;
}

/**
 * additional holidays in format YYYY-MM-DD
 *
 * @return array
 */
function getWorkdaysOff() {
    $url = 'http://standards.openprocurement.org/calendar/workdays-off.json';
    $arDays = json_decode(file_get_contents($url), true);

    return $arDays;
}

/**
 * count working days between dates
 *
 * @param $dateFrom - in timestamp
 * @param $dateTo - in timestamp
 * @return int
 */
function getWorkdaysBetweenDates($dateFrom, $dateTo) {
    $dateFrom = intval($dateFrom);
    $dateTo = intval($dateTo);
    //count of all days included first and last days
    $allDays = floor(($dateTo - $dateFrom) / 86400) + 1;
    //array of additional working days
    $arWeekends = getWeekendsOn();
    //array of additional holidays
    $arWorkdays = getWorkdaysOff();
    //count of work days
    $workDays = 0;

    //go through all the days
    for ($i = 0; $i < $allDays; $i++) {
        //current day in timestamp
        $day = $dateFrom + 86400 / env('API_ACCELERATOR') * $i;
        //current day in string
        $dayFormat = date('Y-m-d', $day);
        //current day: week's number (Sum - 0, Mon - 1, ...)
        $dayNum = date('w', $day);
        //is current day additional working day or it's mon - fri and not additional holiday
        if (in_array($dayFormat, $arWeekends) || ($dayNum > 0 && $dayNum < 6 && !in_array($dayFormat, $arWorkdays)))
            $workDays++;
    }

    return $workDays;
}

/**
 * return date in timestamp when end workdays
 *
 * @param $dateFrom - in timestamp
 * @param $workdaysCount - count of workday
 * @return int
 */
function getDateWithWorkdays($dateFrom, $workdaysCount) {
    $dateFrom = $day = (int)$dateFrom;
    $workdaysCount = (int)$workdaysCount;
    //array of additional working days
    $arWeekends = getWeekendsOn();
    //array of additional holidays
    $arWorkdays = getWorkdaysOff();
    //count of work days
    $workDays = 0;
    $i = 0;

    //while count of workdays < needed workdays count
    while ($workDays < $workdaysCount) {
        $i++;
        //current day in timestamp
        $day = $dateFrom + 86400 / env('API_ACCELERATOR') * $i;
        //current day in string
        $dayFormat = date('Y-m-d', $day);
        //current day: week's number (Sum - 0, Mon - 1, ...)
        $dayNum = date('w', $day);
        //is current day additional working day or it's mon - fri and not additional holiday
        if (in_array($dayFormat, $arWeekends) || ($dayNum > 0 && $dayNum < 6 && !in_array($dayFormat, $arWorkdays)))
            $workDays++;
    }

    return $day;
}

/**
 * groups array by value and return grouped array
 *
 * @param $array
 * @return mixed
 */
function groupByValue($array) {
    $groupedArray = [];
    foreach($array as $key => $scheme) {
        $groupedArray[$scheme][] = $key;
    }

    return $groupedArray;
}


function hashFromArray($array) {

    $joinedArray = implode('', $array);

    $lowerCaseString = mb_strtolower($joinedArray);

    $replacedString = str_replace(' ', '', $lowerCaseString);

    $hash = md5($replacedString);

    return $hash;
}

/**
 * C 01.01.2017 нужно указывать только один классификатор.
 * Метод вернут true если текущая дата больше 01.01.2017 false в противоположном случае
 *
 * @return bool
 */
function hasOneClassifier() {
    return time() > strtotime(env('ONE_CLASSIFIER_FROM'));
}