<?php

class Zefram_View_Helper_HeadScriptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadScript
     */
    protected $_headScript;

    protected function setUp()
    {
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadScript');

        $view = new Zend_View();

        $this->_headScript = new Zefram_View_Helper_HeadScript();
        $this->_headScript->setView($view);
    }

    public function testToStringIndent()
    {
        $this->_headScript->appendScript('Foo');
        $this->_headScript->appendScript('Bar', null, array('noescape' => true));

        $expected = <<<END
<script type="text/javascript">
    //<!--
    Foo
    //-->
</script>
<script type="text/javascript">
    Bar
</script>
END;
        $this->assertEquals($expected, $this->_headScript->toString());
    }

    public function testToStringWithStringWrapper()
    {
        $string = new Zefram_View_Helper_HeadScriptTest_StringWrapper();
        $this->_headScript->appendScript($string);

        $string->value = 'Bar';

        $expected = <<<END
<script type="text/javascript">
    //<!--
    Bar
    //-->
</script>
END;
        $this->assertEquals($expected, $this->_headScript->toString());

        $string->value = 'Baz';

        $expected = <<<END
<script type="text/javascript">
    //<!--
    Baz
    //-->
</script>
END;
        $this->assertEquals($expected, $this->_headScript->toString());
    }
}

class Zefram_View_Helper_HeadScriptTest_StringWrapper
{
    public $value = 'Foo';

    public function __toString()
    {
        return $this->value;
    }
}
