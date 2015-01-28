<?php
    require './php_jsonschema_generator.php';
    define('SYS_PATH', true);
    if ($argc !== 3) {
        echo 'param error.';
        exit;
    }
    $file_in = $argv[1];
    $file_out = $argv[2];

    generator($file_in, $file_out);

    function generator($file_in, $file_out) {
        $data = require $file_in;
        $data_json = json_encode($data);
        if (empty($data_json)) {
            echo 'file_in json_convert error';
            exit;
        }

        $generator = new json_schema_generator($data_json);
        $data_jsonschema = $generator->run();
        file_put_contents($file_out, $data_jsonschema);
    }
