<?php

/**
 * A replacement for {@link Zend_Controller_Action_Helper_ViewRenderer}
 *
 * The original ViewRenderer action helper works improperly with modular
 * applications. In case there are multiple script paths specified in the
 * view, the results of renderScript(), and in consequence postDispatch(),
 * may differ depending on the order of the script paths, which can
 * result in wrong view scripts to be rendered.
 *
 * To fix this instead of directly calling render() on the view object
 * the bundled renderScript() helper with current module name explicitly
 * specified is used.
 *
 * @author xemlock <xemlock@gmail.com>
 */
class Zefram_Controller_Action_Helper_ViewRenderer
    extends Zend_Controller_Action_Helper_ViewRenderer
{
    public function renderScript($script, $name = null)
    {
        if (null === $name) {
            $name = $this->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            // render script in the current module context
            $this->view->renderScript($script, $this->getModule()),
            $name
        );

        $this->setNoRender();
    }
}
