<?php

/**
 * Original HeadScript view helper fails at properly inserting new lines
 * when escaping script contents:
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
 * Moreover in original impl input value whitespaces are not ignored, which
 * can lead to messy indents (contrary to HeadStyle which has proper indent
 * and newline handling)
 */
class Zefram_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * Create script HTML
     *
     * @param  stdClass $item
     * @param  string $indent
     * @param  string $escapeStart
     * @param  string $escapeEnd
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $item = (object) $item;
        $source = isset($item->source) ? $item->source : null;

        if (!empty($source)) {
            $item->source = $source . PHP_EOL;
        }

        $string = parent::itemToString($item, $indent, $escapeStart, $escapeEnd);
        $item->source = $source;

        return $string;
    }
}
