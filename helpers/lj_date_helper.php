<?php

const MAX_PERIOD = 26*7*24*3600;  // 26 weeks

function valid_date_check($date) {
    try {
        new DateTime($date,new DateTimeZone('UTC'));
        return true;
    }
    catch (Exception $e) {
        return false;
    }
}

function decode_start_date($date) {
    if (is_null($date))
        return ((int)(time() / (24*3600)) + 1) * 24*3600 /*Midnight tonight*/ - MAX_PERIOD;

    // Set time to 00:00:00
    return date_create($date . ' 00:00:00',new DateTimeZone('UTC'))->getTimestamp();
}

function decode_end_date($date) {
    if (is_null($date))
        return ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
        
    // Set time to 23:59:59 and add one second
    return date_create($date . ' 23:59:59',new DateTimeZone('UTC'))->getTimestamp() + 1;
}

// Returns number of weeks since Monday 1970-01-05
function time_to_week(integer $time) {
    // UNIX time starts on a Thursday. So move epoch to Monday 1970-01-05
    $monday_offset = 4*24*3600;
    $seconds_per_week = 7*24*3600;
    return floor(($time-$monday_offset) / $seconds_per_week);
}

function timestamp_to_date(integer $d) {
    return date_create("@$d")->format('Y-m-d');
}

