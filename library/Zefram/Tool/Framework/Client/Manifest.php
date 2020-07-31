<?php

/**
 * Replacement for {@link Zend_Tool_Framework_Client_Manifest} that
 * resets 'normalizedActionableMethodShortParams' metadata when there are
 * duplicated short params. This prevents error when attempting to call
 * an action that has two (or more) parameters starting with the same letter.
 *
 * @see https://www.mail-archive.com/fw-general@lists.zend.com/msg23281.html
 *
 * @category   Zefram
 * @package    Zefram_Tool
 */
class Zefram_Tool_Framework_Client_Manifest extends Zend_Tool_Framework_Client_Manifest
{
    /**
     * {@inheritDoc}
     *
     * @return Zend_Tool_Framework_Metadata_Tool[]
     */
    public function getMetadata()
    {
        /** @var Zend_Tool_Framework_Metadata_Tool[] $metadata */
        $metadata = parent::getMetadata();

        // Prevent Zend_Console_Getopt_Exception('Option \"-$flag\" is being defined more than once.')
        // from being thrown when trying to run action that has arguments with
        // names starting with the same letter. If duplicate short parameters
        // are found, remove 'normalizedActionableMethodShortParams' metadata,
        // effectively making this action not callable with short params.
        foreach ($metadata as $index => $metadatum) {
            if ($metadatum->getName() !== 'normalizedActionableMethodShortParams') {
                continue;
            }

            $value = $metadatum->getValue();
            if (count(array_flip($value)) !== count($value)) {
                $metadatum->setValue(array());
            }
        }

        return $metadata;
    }
}
