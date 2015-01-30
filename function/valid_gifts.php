<?php
return function ($gifts) {
    foreach ($gifts->story_gifts as $id) {
        $storeData = Ss_Data::getStoreById($id);
        if (!isset($storeData->sendable) || $storeData->sendable !== true) {
                return "giftid:$id sendable not true in store";
        }
        if (!isset($storeData->not_in_storage) || $storeData->not_in_storage !== true) {
                return "giftid:$id not_in_storage not true in store";
        }
    }
	return true;
};
