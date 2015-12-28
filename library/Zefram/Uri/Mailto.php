<?php

class Zefram_Uri_Mailto extends Zefram_Uri
{
    protected $_validSchemes = array(
        'mailto',
    );

    /**
     * @var Zend_Validate_EmailAddress
     */
    protected $_emailValidator;

    public function valid()
    {
        if (strlen($this->_host) || strlen($this->_username) || strlen($this->_password) || strlen($this->_port)) {
            return false;
        }

        return $this->validateEmail()
            && $this->validateQuery()
            && $this->validateFragment();
    }

    /**
     * Returns true if and only if the email passes validation. If no email is passed,
     * then the email contained in the path variable is used.
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail($email = null)
    {
        if ($email === null) {
            $email = $this->_path;
        }

        return $this->getEmailValidator()->isValid($email);
    }

    /**
     * Retrieve validator for validating email addresses
     *
     * @return Zend_Validate_EmailAddress
     */
    public function getEmailValidator()
    {
        if ($this->_emailValidator === null) {
            $this->_emailValidator = new Zend_Validate_EmailAddress();
        }
        return $this->_emailValidator;
    }
}
