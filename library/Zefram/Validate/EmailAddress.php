<?php

class Zefram_Validate_EmailAddress extends Zend_Validate_EmailAddress
{
    public function setHostnameValidator(Zend_Validate_Hostname $hostnameValidator = null, $allow = Zend_Validate_Hostname::ALLOW_DNS)
    {
        if (!$hostnameValidator) {
            $hostnameValidator = new Zefram_Validate_Hostname($allow);
        }
        return parent::setHostnameValidator($hostnameValidator, $allow);
    }
}
