<?php
return function ($balloon) {
    $nowOpen = $balloon->now_open;
    if (!is_array($nowOpen)) {
        return "now_open not array";
    }
    $startTime = 0;
    $endTime = 0;
    foreach ($nowOpen as $activityId) {
        if (strpos($activityId, ',') !== false) {
            return "now_open id:$activityId error, not include ','";
        }
        if (!property_exists($balloon, $activityId)) {
            return "now_open id:$activityId error, not config detail";
        }
        foreach ($balloon->{$activityId}->rewards as $type => $detail) {
            if ($detail->total < $detail->unit || $detail->unit <= 0) {
                return "$activityId config total&unit error";
            }
        }
        $startTimeTemp = strtotime($balloon->{$activityId}->start_time);
        $endTimeTemp = strtotime($balloon->{$activityId}->end_time);
        if ($endTimeTemp <= $startTimeTemp) {
            return "$activityId start_time, end_time config error";
        }
        if ($startTimeTemp <= $endTime) {
            return "$activityId and pre activity time config error";
        }
        $startTime = $startTimeTemp;
        $endTime = $endTimeTemp;
    }

	return true;
};
