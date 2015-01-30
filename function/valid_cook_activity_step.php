<?php
return function ($cookAcitivityStep) {
    $cookBook = require CONF_PATH . 'cookbook.php';
    $cookActivity = require CONF_PATH . 'cook_activity.php';
    $endTime = strtotime($cookActivity['activity'][$cookActivity['available_activity_id']]['end_time']);
    foreach ($cookAcitivityStep as $stepNo => $table) {
        foreach ($table->table as $tableNo => $tableDetail) {
            foreach ($tableDetail as $cookNo => $cookDetail) {
                if (Ss_Data::isInStore($cookDetail->id) === false) {
                    return "stepid:$stepNo, tableid:$tableNo, cookid:$cookNo, itemid:".$cookDetail->id." not in store";
                }
                if (Ss_Data::isInRv($cookDetail->id) === false) {
                    return "stepid:$stepNo, tableid:$tableNo, cookid:$cookNo, itemid:".$cookDetail->id." not in resourceversion";
                }
                if ($cookDetail->limit < $cookBook[$cookDetail->id]['unlock_level']) {
                    return "stepid:$stepNo, tableid:$tableNo, cookid:$cookNo, itemid:".$cookDetail->id." limit big than unlock_level in cookbook";
                }
                $storeData = Ss_Data::getStoreById($cookDetail->id);
                if (isset($storeData->limit_config)) {
                    if ($endTime > $storeData->limit_config['start_time'] + $storeData->limit_config['duration']) {
                        return "stepid:$stepNo, tableid:$tableNo, cookid:$cookNo, itemid:".$cookDetail->id." limit_config time big than activity end_time";
                    }
                }
            }
        }
    }
	return true;
};
