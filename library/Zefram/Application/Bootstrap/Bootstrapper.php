<?php

interface Zefram_Application_Bootstrap_Bootstrapper
    extends Zend_Application_Bootstrap_Bootstrapper
{
    /**
     * Is the requested class resource present?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasClassResource($resource);
}
