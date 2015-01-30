<?php
class Ss_Data {
    public static $storeData = null;
    public static $storyData = null;
    public static $rvData = null;
    public static $rvText = null;
    public static function getStoreData() {
        if ( self::$storeData === null ) {
            self::$storeData = (array) require CONF_PATH . '../i18n/en/storestatic.php';
        }
        return self::$storeData;
    }
    public static function getStoreById($id) {
        $storeData = self::getStoreData();
        return isset($storeData[$id]) ? $storeData[$id] : new stdClass();
    }
    public static function getStoryData() {
        if ( self::$storyData === null ) {
            self::$storyData = (array) require CONF_PATH . '../i18n/en/storystatic.php';
        }
        return self::$storyData;
    }
    public static function getStoryById($id) {
        $storyData = self::getStoryData();
        return isset($storyData[$id]) ? $storyData[$id] : new stdClass();
    }
    public static function getRvData() {
        if ( self::$rvData === null ) {
            self::$rvData = (array) require CONF_PATH . 'resourceVersion.php';
        }
        return self::$rvData;
    }
    public static function getRvText() {
        if ( self::$rvText === null ) {
            self::$rvText = file_get_contents(CONF_PATH . 'resourceVersion.php', 'r');
        }
        return self::$rvText;
    }
    public static function isInStore($id) {
        $storeData = self::getStoreData();
        return array_key_exists($id, $storeData) ? true : false;
    }
    public static function isInStory($id) {
        $storyData = self::getStoryData();
        return array_key_exists($id, $storyData) ? true : false;
    }
    public static function isInRv($id) {
        $rvText = self::getRvText();
        return strpos($rvText, '/'.$id.'.') === false ? false : true;
    }

    public static function destruct() {
        if (self::$storeData !== null) {
            unset(self::$storeData);
        }
        if (self::$storyData !== null) {
            unset(self::$storyData);
        }
        if (self::$rvData !== null) {
            unset(self::$rvData);
        }
    }

}
