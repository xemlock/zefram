<?php

/**
 * Original HeadScript view helper fails at properly inserting new lines when escaping script contents:
 *
 * Original impl of toString() when escaping is on:
 * <script>
 *     //<!--
 *     CONTENT    //-->
 * </script>
 *
 * Desired output:
 * <script>
 *     //<!--
 *     CONTENT
 *     //-->
 * </script>
 *
 * When escaping is off (noescape attribute present and equal FALSE):
 * <script>
 *     SCRIPT</script>
 *
 * Desired output:
 * <script>
 *     SCRIPT
 * </script>
 *
 * Moreover in original impl input value whitespaces are not ignored, which can lead to messy indents
 * (contrary to HeadStyle which has proper indent and newline handling)
 */
class Zefram_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * {@inheritDoc}
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        if ($content !== null && ($content = trim($content)) !== '') {
            $content .= PHP_EOL;
        }
        return parent::createData($type, $attributes, $content);
    }
}
