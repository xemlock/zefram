<?php

class Zefram_View_Helper_HeadScriptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadScript
     */
    protected $_helper;

    protected function setUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadScript');

        $this->_helper = new Zefram_View_Helper_HeadScript();
        $this->_helper->setView(new Zend_View());
    }

    public function testToStringIndent()
    {
        $this->_helper->appendScript('Foo');
        $this->_helper->appendScript('Bar', null, array('noescape' => true));

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
        $this->assertEquals($expected, $this->_helper->toString());
    }

    public function testToStringWithStringWrapper()
    {
        $string = new Zefram_View_Helper_HeadScriptTest_StringWrapper();
        $this->_helper->appendScript($string);

        $string->value = 'Bar';

        $expected = <<<END
<script type="text/javascript">
    //<!--
    Bar
    //-->
</script>
END;
        $this->assertEquals($expected, $this->_helper->toString());

        $string->value = 'Baz';

        $expected = <<<END
<script type="text/javascript">
    //<!--
    Baz
    //-->
</script>
END;
        $this->assertEquals($expected, $this->_helper->toString());
    }

    public function testItemToString()
    {
        $this->_helper->appendFile('foo.js');
        $this->assertEquals('<script type="text/javascript" src="foo.js"></script>', $this->_helper->toString());
    }

    public function testItemToStringEscapeScriptEndTag()
    {
        $this->_helper->appendScript("console.log('</script>');", null, array('noescape' => true));

        $expected =<<<END
<script type="text/javascript">
    console.log('<\/script>');
</script>
END;
        $this->assertEquals($expected, $this->_helper->toString());
    }

    public function testToStringWithEmptyScripts()
    {
        $this->_helper->appendFile('foo.js');
        $this->_helper->appendScript('');
        $this->_helper->appendFile('bar.js');

        $this->assertEquals(
            '<script type="text/javascript" src="foo.js"></script>' . PHP_EOL
            . '<script type="text/javascript" src="bar.js"></script>',
            $this->_helper->toString()
        );
    }

    public function testDefaultNoEcapeInHtml5()
    {
        $this->_helper->appendScript('Foo');
        $this->_helper->view->doctype()->setDoctype(Zend_View_Helper_Doctype::HTML5);

        $expected =<<<END
<script>
    Foo
</script>
END;
        $this->assertEquals($expected, $this->_helper->toString());
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
