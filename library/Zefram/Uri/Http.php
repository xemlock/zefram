<?php

class Zefram_Uri_Http extends Zefram_Uri
{
    protected $_validSchemes = array(
        'http',
        'https',
    );

    public function valid()
    {
        if (strlen($this->_host) === 0) {
            return false;
        }
        return parent::valid();
    }
}
