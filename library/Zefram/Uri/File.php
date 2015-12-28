<?php

class Zefram_Uri_File extends Zefram_Uri
{
    protected $_validSchemes = array(
        'file',
    );

    public function valid()
    {
        if (strlen($this->_username) || strlen($this->_password) || strlen($this->_query) || strlen($this->_fragment)) {
            return false;
        }
        return parent::valid();
    }

    // TODO validate filesystem path
}
