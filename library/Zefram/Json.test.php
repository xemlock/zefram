<?php

echo '<pre>';
$a = array('a' => array('b' => 'B', 'c' => "C\nD", 'd"' => array(1,2,3,4,5), 'e' => 32));
var_dump(Zend_Json::prettyPrint(Zend_Json::encode($a))); 
var_dump(Zefram_Json::prettyPrint(Zend_Json::encode($a))); 

echo 'EXPECT:', "\n", <<<END_EXPECT
string(160) "{
    "a": {
        "b": "B",
        "c": "C\\nD",
        "d\"": [
            1,
            2,
            3,
            4,
            5
        ]
    }
}"
END_EXPECT;

