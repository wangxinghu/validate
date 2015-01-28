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
    public static function getStoryData() {
        if ( self::$storyData === null ) {
            self::$storyData = (array) require CONF_PATH . '../i18n/en/storystatic.php';
        }
        return self::$storyData;
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
