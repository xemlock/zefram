<?php

class Zefram_JsonTest extends PHPUnit_Framework_TestCase
{
    public function testPrettyPrint()
    {
        $array = array(
            'a' => array(
                'b' => 'B',
                'c' => "C\nD",
                'd"' => array(1, 2, 3, 4, 5),
                'e' => 32,
            ),
        );
        $json = <<<END_JSON
{
    "a": {
        "b": "B",
        "c": "C\\nD",
        "d\"": [
            1,
            2,
            3,
            4,
            5
        ],
        "e": 32
    }
}
END_JSON;
        $json = str_replace("\r\n", "\n", $json);

        $this->assertEquals($json, Zefram_Json::prettyPrint(Zend_Json::encode($array)));
        $this->assertEquals($json, Zefram_Json::prettyPrint(Zefram_Json::encode($array)));
    }
}
