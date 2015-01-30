<?php
    class json_schema_generator {
        private $data_json;
        private $data_jsonschema;
        public function __construct($data_json) {
            $this->data_json = $data_json;
            $this->data_jsonschema = array();
            $this->data_jsonschema['$schema'] = 'http://json-schema.org/draft-04/schema#';
        }
        public function check() {
            $data_array = json_decode($this->data_json, true);
            if (empty($data_array) || !is_array($data_array)) {
                throw new exception('data_json decode error');
            }
            return $data_array;
        }

        public function is_assoc($arr_data) {
            if(is_array($arr_data)) {
                $keys = array_keys($arr_data);
                return $keys !== array_keys($keys);
            }
            return false;
        }
        public function run() {
            $data_array = $this->check();
            $ret = $this->generator('/', $data_array);
            $this->data_jsonschema += $ret;
            $data_jsonschema_format = $this->jsonFormat($this->data_jsonschema);
            return $data_jsonschema_format;
        }

        public function generator($key, $value) {
            $ret = array();
            $type = $this->checkType($value);
            if ($type === false) {
                throw new exception('type generator error');
            }
            $ret['id'] = $key;
            $ret['type'] = $type;
            if ($type === 'array') {
                $num = count($value);
                foreach ($value as $subkey => $subvalue) {
                    if ($num === 1) {
                        $ret['items'] = $this->generator($subkey, $subvalue);
                    }else{
                        $ret['items'][] = $this->generator($subkey, $subvalue);
                    }
                }
            }elseif($type === 'object') {
                foreach ($value as $subkey => $subvalue) {
                    $ret['properties'][$subkey] = $this->generator($subkey, $subvalue);
                }
            }else{
            }
            return $ret;
        }
        public function checkType($value) {
            if (is_bool($value)) {
                return 'boolean';
            }
            if (is_object($value) || is_array($value)) {
                $value = (array)$value;
                if ($this->is_assoc($value)) {
                    return 'object';
                }else{
                    return 'array';
                }
            }
            if (is_int($value)) {
                return 'integer';
            }
            if (is_string($value)) {
                return 'string';
            }
            if (is_float($value)) {
                return 'numeric';
            }
            if (is_null($value)) {
                return 'null';
            }
            return false;
        }
        /** Json数据格式化
         * * @param  Mixed  $data   数据
         * * @param  String $indent 缩进字符，默认4个空格
         * * @return JSON
         * */
        public function jsonFormat($data, $indent=null){

            // 对数组中每个元素递归进行urlencode操作，保护中文字符
            array_walk_recursive($data, array($this, 'jsonFormatProtect'));

            // json encode
            $data = json_encode($data);

            // 将urlencode的内容进行urldecode
            $data = urldecode($data);

            // 缩进处理
            $ret = '';
            $pos = 0;
            $length = strlen($data);
            $indent = isset($indent)? $indent : '    ';
            $newline = "\n";
            $prevchar = '';
            $outofquotes = true;

            for($i=0; $i<=$length; $i++){

                $char = substr($data, $i, 1);

                if($char=='"' && $prevchar!='\\'){
                    $outofquotes = !$outofquotes;
                }elseif(($char=='}' || $char==']') && $outofquotes){
                    $ret .= $newline;
                    $pos --;
                    for($j=0; $j<$pos; $j++){
                        $ret .= $indent;
                    }
                }

                $ret .= $char;

                if(($char==',' || $char=='{' || $char=='[') && $outofquotes){
                    $ret .= $newline;
                    if($char=='{' || $char=='['){
                        $pos ++;
                    }

                    for($j=0; $j<$pos; $j++){
                        $ret .= $indent;
                    }
                }

                $prevchar = $char;
            }

            return $ret;
        }

        /** 将数组元素进行urlencode
         * @param String $val
         */
        public function jsonFormatProtect(&$val){
            if($val!==true && $val!==false && $val!==null){
                $val = urlencode($val);
            }
        }
    }

