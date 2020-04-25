<?php

/**
 * Enhanced implementation of Navigation Menu view helper.
 *
 * The helper implementation provided by the Zend Framework {@link Zend_View_Helper_Navigation}
 * offers no way of managing custom HTML attributes on the menu LI elements.
 * This limitation was partially fixed in 1.12.1 when 'addPageClassToLi' option
 * was added. This however still does not allow to set 'class' attributes on A
 * and LI elements at the same time. There is also no possibility to add other
 * HTML attributes on LI and UL elements, which limits the usefulness of the
 * helper in real-world applications. And the most striking proof of that is
 * that there is no way of producing a Bootstrap-compatible, or an a11y
 * compliant dropdown menu.
 *
 * This implementation adds support for the following page properties that will
 * be used when rendering LI elements:
 * - liClass - string to be added as 'class' attribute to LI element regardless
 *   of the 'addPageClassToLi' option
 * - liHtmlAttribs - array of custom HTML attributes to be added to LI element
 * - element - string with HTML tag name to be used for rendering menu item, if
 *   not provided, it will use 'a' when page returns non-empty href or 'span'
 *   otherwise
 * - escapeLabel - a boolean indicating whether to escape menu item label text
 * - ulClass - string to be used as 'class' attribute of the subpages UL
 * - ulId - string to be used as 'id' attribute of the UL with subpages UL
 * - ulHtmlAttribs - array of custom HTML attributes to be added to the
 *   subpages UL
 *
 * The following new property has been added to the helper:
 * - ulHtmlAttribs - array of custom HTML attributes to be added to the
 *   top-level UL element
 *
 * @package    Zefram_View
 * @subpackage Helper
 * @author     xemlock
 *
 * @property Zend_View_Abstract|Zefram_View_Abstract $view
 */
class Zefram_View_Helper_Navigation_Menu extends Zend_View_Helper_Navigation_Menu
{
    /**
     * Whether labels should be escaped.
     *
     * @var bool
     */
    protected $_escapeLabels = true;

    /**
     * Custom HTML attributes to use for the top-level UL element.
     *
     * @var array
     */
    protected $_ulHtmlAttribs = array();

    /**
     * Sets a flag indicating whether labels should be escaped.
     *
     * @param  bool $flag OPTIONAL escape labels
     * @return $this
     */
    public function escapeLabels($flag = true)
    {
        $this->_escapeLabels = (bool) $flag;
        return $this;
    }

    /**
     * @return array
     */
    public function getUlHtmlAttribs()
    {
        return $this->_ulHtmlAttribs;
    }

    /**
     * @param array $ulHtmlAttribs
     * @return $this
     */
    public function setUlHtmlAttribs(array $ulHtmlAttribs = array())
    {
        $this->_ulHtmlAttribs = $ulHtmlAttribs;
        return $this;
    }

    /**
     * Renders the deepest active menu within [$minDepth, $maxDeth], (called
     * from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container     container to render
     * @param  string                    $ulClass       CSS class for first UL
     * @param  string                    $indent        initial indentation
     * @param  string                    $innerIndent   inner indentation
     * @param  int|null                  $minDepth      minimum depth
     * @param  int|null                  $maxDepth      maximum depth
     * @param  string|null               $ulId          unique identifier (id)
     *                                                  for first UL
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     * @param  string|null               $activeClass       CSS class for active
     *                                                      element
     * @param  string                    $parentClass       CSS class for parent
     *                                                      li's
     * @param  bool                      $renderParentClass Render parent class?
     * @return string                                       rendered menu (HTML)
     */
    protected function _renderDeepestMenu(Zend_Navigation_Container $container,
                                          $ulClass,
                                          $indent,
                                          $innerIndent,
                                          $minDepth,
                                          $maxDepth,
                                          $ulId,
                                          $addPageClassToLi,
                                          $activeClass,
                                          $parentClass,
                                          $renderParentClass)
    {
        if (!$active = $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }

        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (!$active['page']->hasPages()) {
                return '';
            }
        } else if (!$active['page']->hasPages()) {
            // found pages has no children; render siblings
            $active['page'] = $active['page']->getParent();
        } else if (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
            // children are below max depth; render siblings
            $active['page'] = $active['page']->getParent();
        }

        $attribs = array(
            'class' => $ulClass,
            'id'    => $ulId,
        ) + $this->_ulHtmlAttribs;

        // We don't need a prefix for the menu ID (backup)
        $skipValue = $this->_skipPrefixForId;
        $this->skipPrefixForId();

        $html = $indent . '<ul'
            . $this->_htmlAttribs($attribs)
            . '>'
            . $this->getEOL();

        // Reset prefix for IDs
        $this->_skipPrefixForId = $skipValue;

        foreach ($active['page'] as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }

            $liClass = array();
            if ($subPage->isActive(true)) {
                $liClass[] = $activeClass;
            }
            if ($addPageClassToLi && $subPage->getClass()) {
                $liClass[] = $subPage->getClass();
            }
            $html .= $this->_renderListItem($subPage, $liClass, $indent, $innerIndent);
            $html .= $indent . $innerIndent . '</li>' . $this->getEOL();
        }

        $html .= $indent . '</ul>';

        return $html;
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container     container to render
     * @param  string                    $ulClass       CSS class for first UL
     * @param  string                    $indent        initial indentation
     * @param  string                    $innerIndent   inner indentation
     * @param  int|null                  $minDepth      minimum depth
     * @param  int|null                  $maxDepth      maximum depth
     * @param  bool                      $onlyActive    render only active branch?
     * @param  bool                      $expandSibs    render siblings of active
     *                                                  branch nodes?
     * @param  string|null               $ulId          unique identifier (id)
     *                                                  for first UL
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     * @param  string|null               $activeClass       CSS class for active
     *                                                      element
     * @param  string                    $parentClass       CSS class for parent
     *                                                      li's
     * @param  bool                      $renderParentClass Render parent class?
     * @return string                                       rendered menu (HTML)
     */
    protected function _renderMenu(Zend_Navigation_Container $container,
                                   $ulClass,
                                   $indent,
                                   $innerIndent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $expandSibs,
                                   $ulId,
                                   $addPageClassToLi,
                                   $activeClass,
                                   $parentClass,
                                   $renderParentClass)
    {
        $html = '';

        // find deepest active
        if ($found = $this->findActive($container, $minDepth, $maxDepth)) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if ($expandSibs && $depth > $minDepth) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($page->getParent()->isActive(true)) {
                        // page is a sibling of the active branch...
                        $accept = true;
                    }
                }
                if (!$isActive && !$accept) {
                    continue;
                }
            } else if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth   -= $minDepth;
            $myIndent = $indent . str_repeat($innerIndent, $depth * 2);

            if ($depth > $prevDepth) {
                $attribs = array();

                // start new ul tag
                if (0 == $depth) {
                    $attribs = array(
                        'class' => $ulClass,
                        'id'    => $ulId,
                    ) + $this->_ulHtmlAttribs;
                } else {
                    $parentPage = $page->getParent();
                    if ($parentPage->get('ulClass')) {
                        $attribs['class'] = $parentPage->get('ulClass');
                    }
                    if ($parentPage->get('ulId')) {
                        $attribs['id'] = $this->_normalizeId($parentPage->get('ulId'));
                    }
                    if (is_array($parentPage->get('ulHtmlAttribs'))) {
                        $attribs += $parentPage->get('ulHtmlAttribs');
                    }
                }

                // We don't need a prefix for the menu ID (backup)
                $skipValue = $this->_skipPrefixForId;
                $this->skipPrefixForId();

                $html .= $myIndent . '<ul'
                    . $this->_htmlAttribs($attribs)
                    . '>'
                    . $this->getEOL();

                // Reset prefix for IDs
                $this->_skipPrefixForId = $skipValue;
            } else if ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind   = $indent . str_repeat($innerIndent, $i * 2);
                    $html .= $ind . $innerIndent . '</li>' . $this->getEOL();
                    $html .= $ind . '</ul>' . $this->getEOL();
                }
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            } else {
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            }

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($isActive) {
                $liClasses[] = $activeClass;
            }
            // Add CSS class from page to LI?
            if ($addPageClassToLi && $page->getClass()) {
                $liClasses[] = $page->getClass();
            }
            // Add CSS class for parents to LI?
            if ($renderParentClass && $page->hasChildren()) {
                // Check max depth
                if ((is_int($maxDepth) && ($depth + 1 < $maxDepth))
                    || !is_int($maxDepth)
                ) {
                    $liClasses[] = $parentClass;
                }
            }

            $html .= $this->_renderListItem($page, $liClasses, $myIndent, $innerIndent);

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth+1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat($innerIndent . $innerIndent, $i - 1);
                $html    .= $myIndent . $innerIndent . '</li>' . $this->getEOL()
                    . $myIndent . '</ul>' . $this->getEOL();
            }
            $html = rtrim($html, $this->getEOL());
        }

        return $html;
    }

    /**
     * Renders menu list item
     *
     * Duplicate code extracted from {@link _renderDeepestMenu()} and {@link _renderMenu()}.
     *
     * @param Zend_Navigation_Page $page
     * @param array $liClasses
     * @param string $indent
     * @param string $innerIndent
     * @return string
     */
    protected function _renderListItem(Zend_Navigation_Page $page, array $liClasses, $indent, $innerIndent)
    {
        if ($page->get('liClass')) {
            $liClasses[] = $page->get('liClass');
        }

        $liHtmlAttribs = is_array($page->get('liHtmlAttribs')) ? $page->get('liHtmlAttribs') : array();
        $liHtmlAttribs['class'] = implode(' ', $liClasses);

        $html = $indent . $innerIndent . '<li'
            . $this->_htmlAttribs($liHtmlAttribs)
            . '>' . $this->getEOL();

        $pageHtml = trim($this->htmlify($page));
        if (strlen($pageHtml)) {
            $html .= $indent . str_repeat($innerIndent, 2)
                . $pageHtml
                . $this->getEOL();
        }

        return $html;
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty.
     *
     * If the page provides a boolean 'escapeLabel' property, then its value
     * will be used for determining whether page label should be escaped.
     *
     * Overrides {@link Zend_View_Helper_Navigation_Menu::htmlify()}.
     *
     * @param  Zend_Navigation_Page $page  page to generate HTML for
     * @return string                      HTML string for the given page
     */
    public function htmlify(Zend_Navigation_Page $page)
    {
        if ($partial = $page->get('partial')) {
            return $this->_renderPagePartial($page, $partial);
        }

        // get attribs for element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $this->_translate($page->getTitle()),
        );

        if (false === $this->getAddPageClassToLi()) {
            $attribs['class'] = $page->getClass();
        }

        if ($page->get('element')) {
            $element = $page->get('element');
        } elseif ($page->getHref()) {
            $element = 'a';
        } else {
            $element = 'span';
        }

        if ($element === 'a') {
            $attribs['href']      = $page->getHref();
            $attribs['target']    = $page->getTarget();
            $attribs['accesskey'] = $page->getAccessKey();
        }

        // Add custom HTML attributes
        $attribs = array_merge($attribs, $page->getCustomHtmlAttribs());

        $html = '<' . $element . $this->_htmlAttribs($attribs) . '>';
        $label = $this->_translate($page->getLabel());

        $escapeLabel = is_bool($page->get('escapeLabel')) ? $page->get('escapeLabel') : $this->_escapeLabels;
        if ($escapeLabel) {
            $html .= $this->view->escape($label);
        } else {
            $html .= $label;
        }

        $html .= '</' . $element . '>';
        return $html;
    }

    /**
     * @param Zend_Navigation_Page $page
     * @param string|string[] $partial
     * @return mixed
     * @throws Zend_View_Exception
     */
    protected function _renderPagePartial(Zend_Navigation_Page $page, $partial)
    {
        $model = array('page' => $page);

        if (is_array($partial)) {
            if (count($partial) != 2) {
                $e = new Zend_View_Exception(
                    'Unable to render menu: A view partial supplied as '
                    . 'an array must contain two values: partial view '
                    . 'script and module where script can be found'
                );
                $e->setView($this->view);
                throw $e;
            }

            return $this->view->partial($partial[0], $partial[1], $model);
        }

        return $this->view->partial($partial, null, $model);
    }

    /**
     * Translate a message (for label, title, ...)
     *
     * @param  string $message  ID of the message to translate
     * @return string           Translated message
     */
    protected function _translate($message)
    {
        $translator = $this->getUseTranslator() ? $this->getTranslator() : null;
        return $translator ? $translator->translate($message) : $message;
    }
}
