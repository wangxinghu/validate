<?php
return function ($calendar) {
    $bigReward = $calendar->big_reward;
    $storeData = Ss_Data::getStoreData();
    $rvText = Ss_Data::getRvText();

    foreach ($bigReward as $m => $itemId) {
        if (!array_key_exists($itemId, $storeData)) {
            return "big_reward month:$m itemId:$itemId not in store";
        }
        if (strpos($rvText, '/'.$itemId.'.zip') === false) {
            return "big_reward month:$m itemId:$itemId not in resourceversion";
        }
    }
    $curMonth = date('n', time());
    $monthNext = ($curMonth == 12) ? 1 : ($curMonth + 1);
    $monthNextNext = ($monthNext == 12) ? 1 : ($monthNext + 1);
    if ($bigReward->{$curMonth} == $bigReward->{$monthNext}) {
        return "big_reward month $curMonth, $monthNext itemid equal";
    }
    if ($bigReward->{$monthNext} == $bigReward->{$monthNextNext}) {
        return "big_reward month $monthNext, $monthNextNext itemid equal";
    }

	return true;
};
