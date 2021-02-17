<?php

class Zefram_View_Helper_FormTextarea extends Zend_View_Helper_FormTextarea
{
    /**
     * The default number of rows for a textarea
     *
     * The value used here is the default used by browsers, not the
     * ridiculous value provided in {@link Zend_View_Helper_FormTextarea::$rows}.
     *
     * @var int
     */
    public $rows = 2;
}
