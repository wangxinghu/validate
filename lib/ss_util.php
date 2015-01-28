<?php
class Ss_Util {
    public static function CheckSyntax($fileName, $checkIncludes = true) {
        if(!is_file($fileName) || !is_readable($fileName)) {
            throw new Exception("Cannot read file ".$fileName);
        }
        $fileName = realpath($fileName);
        $output = shell_exec('php -l "'.$fileName.'"');
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

        if($count > 0) {
            throw new Exception(trim($syntaxError));
        }

        if($checkIncludes) {
            foreach(self::GetIncludes($fileName) as $include) {
                self::CheckSyntax($include);
            }
        }
    }

    public static function GetIncludes($fileName) {
        $includes = array();
        $dir = dirname($fileName);
        $requireSplit = array_slice(preg_split('/require|include/i', file_get_contents($fileName)), 1);

        foreach($requireSplit as $string) {
            $string = substr($string, 0, strpos($string, ";"));

            if(strpos($string, "$") !== false) {
                continue;
            }
            $quoteSplit = preg_split('/[\'"]/', $string);
            if($include = $quoteSplit[1]) {
                if(strpos($include, ':') === FALSE) {
                    $include = realpath($dir.DIRECTORY_SEPARATOR.$include);
                }
                array_push($includes, $include);
            }
        }
        return $includes;
    }
}
