<?php

class Zefram_Uri_File extends Zefram_Uri
{
    protected $_validSchemes = array(
        'file',
    );

    public function validateHost($host = null)
    {
        if ($host === null) {
            $host = $this->_host;
        }

        // Empty host is still considered valid
        if (strlen($host) === 0) {
            return true;
        }

        return parent::validateHost($host);
    }

    public function validateQuery($query = null)
    {
        if ($query === null) {
            $query = $this->_query;
        }

        return (strlen($query) === 0);
    }
}
