<?php

class Zefram_Validate_GreaterThanContextTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {}

    /**
     * Data provider for {@link testValidate()}.
     *
     * @return array
     */
    public function dataForValidate()
    {
        return array(
            // value min inclusive expected
            array(0, 1, false, false),
            array(1, 0, false, true),
            array(1, 1, false, false),
            array(0, 1, true,  false),
            array(1, 0, true,  true),
            array(1, 1, true,  true),

            array('alpha',  'beta', false, false),
            array('beta',  'alpha', false, true),
            array('beta',   'beta', false, false),
            array('alpha',  'beta', true,  false),
            array('beta',  'alpha', true,  true),
            array('beta',   'beta', true,  true),

            array(0,    null, false, false),
            array(null,    0, false, false),
            array(null, null, false, false),
            array(0,    null, true,  false),
            array(null,    0, true,  true),
            array(null, null, true,  false),
        );
    }

    /**
     * Tests correct behavior of the validator constructor.
     */
    public function testConstructor()
    {
        $validator = new Zefram_Validate_GreaterThanContext('min', true);
        $this->assertEquals('min', $validator->getContextKey());
        $this->assertTrue($validator->getInclusive());

        $validator = new Zefram_Validate_GreaterThanContext(array(
            'contextKey' => 'min',
            'inclusive'  =>  true,
        ));
        $this->assertEquals('min', $validator->getContextKey());
        $this->assertTrue($validator->getInclusive());
    }

    /**
     * Tests if context key option can be changed.
     */
    public function testContextKey()
    {
        $validator = new Zefram_Validate_GreaterThanContext('min1');
        $validator->setContextKey('min2');
        $this->assertEquals('min2', $validator->getContextKey());
    }

    /**
     * Tests if inclusive option can be changed.
     */
    public function testInclusive()
    {
        $validator = new Zefram_Validate_GreaterThanContext('min', true);
        $validator->setInclusive(false);
        $this->assertFalse($validator->getInclusive());
    }

    /**
     * Tests if no context key provided to the constructor results in
     * an exception.
     *
     * @expectedException Zend_Validate_Exception
     */
    public function testEmptyContextKey()
    {
        $validator = new Zefram_Validate_GreaterThanContext();
    }

    /**
     * Tests validation.
     *
     * @dataProvider dataForValidate
     */
    public function testValidate($value, $min, $inclusive, $expected)
    {
        $validator = new Zefram_Validate_GreaterThanContext('min', $inclusive);
        $this->assertEquals(
            $expected,
            $validator->isValid($value, compact('min')),
            "value: $value -- min: $min -- inclusive: $inclusive"
        );
    }
}
