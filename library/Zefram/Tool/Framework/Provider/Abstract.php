<?php

/**
 * Abstract provider with built-in name resolver.
 *
 * Implementation rationale: Provider name retrieval algorithm in
 * {@link Zend_Tool_Framework_Provider_Signature::_processName()}
 * can't properly handle namespaces (they are not stripped), as well as
 * class names which, after PSR-0 path part removed, are either 'Provider'
 * or 'Manifest'.
 */
class Zefram_Tool_Framework_Provider_Abstract extends Zend_Tool_Framework_Provider_Abstract
{
    /**
     * Get provider name
     *
     * @return string
     */
    public function getName()
    {
        $name = get_class($this);

        if (false !== ($pos = strrpos($name, '_'))) {
            $name = substr($name, $pos + 1);
        }

        if (false !== ($pos = strrpos($name, '\\'))) {
            $name = substr($name, $pos + 1);
        }

        if ($name !== 'Provider' && $name !== 'Manifest') {
            $name = preg_replace('#(Provider|Manifest)$#', '', $name);
        }

        return $name;
    }
}
