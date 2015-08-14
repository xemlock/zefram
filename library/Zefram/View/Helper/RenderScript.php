<?php

/**
 * View helper to render scripts across different modules.
 *
 * This implementation has two advantages over Partial view helper (ZF 1.12):
 * - it does not clone view to render script
 * - it correctly determines module views directory based on the output of
 *   Zend_Controller_Action_Helper_ViewRenderer::getViewBasePathSpec(), not
 *   by using a hard-coded directory name
 * - works in the current scope (but can also work in a clean one)
 * - script file extension, if not present, will be appended
 * - view scripts from default module may override view scripts in other
 *   modules
 *
 * Main problem with this helper is too much (potentially re-usable) code
 * related to application/module structure, that should be a part of an action
 * helper rather than a view helper (a ViewRenderer would be my guess).
 *
 * @package Zefram_View
 * @subpackage Helper
 * @author xemlock
 */
class Zefram_View_Helper_RenderScript extends Zend_View_Helper_Abstract
{
    /**
     * Flag indicating whether variables from current view context should be
     * cleared before rendering view script in {@link renderScript()}
     *
     * @var bool
     */
    protected $_clearVars = false;

    /**
     * Render a given view script
     *
     * If no arguments are passed, the helper instance is returned.
     *
     * @param  string $script
     * @param  string|array $module OPTIONAL
     * @param  array $vars OPTIONAL
     * @return string|Zefram_View_Helper_RenderScript
     */
    public function renderScript($script = null, $module = null, array $vars = null)
    {
        if (func_get_args() === 0) {
            return $this;
        }

        // if module is an array treat it as view variables
        if (is_array($module)) {
            $vars = $module;
            $module = null;
        }

        $viewRenderer = $this->_getViewRenderer();

        $viewBasePath = null;
        $viewScriptPath = null;

        // if module name is not explicitly given, use the current module
        if ($module === null) {
            $module = $viewRenderer->getModule();
        }

        // if given module differs from the current module, prepare the base
        // path of the given module to be added to the view
        if ($module !== $viewRenderer->getModule()) {
            $viewBasePath = $this->_getModuleViewBasePath($module);
        }

        // if given module is not the default module, prepare script path
        // for view script overriding :defaultModuleDir/views/modules/:module
        if ($module !== ($defaultModule = $this->_getDefaultModule())) {
            $scriptPath = $this->_getModuleViewBasePath($defaultModule) . '/modules/' . $module;
        }

        $view = $this->view;

        // viewState stores the original script paths and vars, so that they
        // can be restored during cleanup
        $viewState = array(
            'scriptPaths' => null,
            'vars' => null,
        );

        // if view needs to be modified, populate viewState
        if ($viewBasePath !== null || $scriptPath !== null) {
            // view script paths will be modified, store original scriptPaths
            // in the viewState
            $viewState['scriptPaths'] = $view->getScriptPaths();

            // add base paths for scripts, helpers and filters
            // no need to clone plugin loaders because:
            // - partial view helper also pollutes viewScripts and loaders
            //   paths (yeah, it's hardly an argument)
            // - if class prefix is specified the plugin lookup penalty will
            //   be (almost) negligible
            if ($viewBasePath !== null) {
                $view->addBasePath($viewBasePath, $this->_getModuleClassPrefix($module) . 'View_');
            }

            // add path to script from the default module that will override
            // the script from the given module
            if ($scriptPath !== null) {
                $view->addScriptPath($viewScriptPath);
            }
        }

        if ($this->_clearVars) {
            // cleanScope is reset after each call to renderScript(), so that
            // it must be explicitly set before each call (this also applies
            // to recursive renderScript() calls)
            $this->_clearVars = false;

            $viewState['vars'] = $view->getVars();
            $view->clearVars();

        } elseif ($vars) {
            // save original values of variables which will be overwritten, so
            // that they can be restored during cleanup
            $viewState['vars'] = array_intersect_key($view->getVars(), $vars);
        }

        if ($vars) {
            $view->assign($vars);
        }

        try {
            $exception = null;
            $result = $view->render($this->_getScriptName($script));

        } catch (Exception $exception) {
            // will be re-thrown after cleanup
        }

        // restore view from the viewState, unset all variables from vars
        if ($vars) {
            foreach ($vars as $key => $value) {
                unset($view->{$key});
            }
        }

        $this->_setViewState($view, $viewState);

        if ($exception) {
            throw $exception;
        }

        return $result;
    }

    /**
     * Set flag indicating whether variables from current view context should
     * be cleared before rendering view script
     *
     * @param bool $flag
     * @return $this
     */
    public function setClearVars($flag)
    {
        $this->_clearVars = (bool) $flag;
        return $this;
    }

    /**
     * Whether variables from current view context should be cleared before
     * rendering view script
     *
     * @return bool
     */
    public function getClearVars()
    {
        return $this->_clearVars;
    }

    /**
     * Get ViewRenderer action helper instance
     *
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    protected function _getViewRenderer()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
    }

    /**
     * Ensure that given script has proper suffix (file extension)
     *
     * @param  string $script
     * @return string
     */
    protected function _getScriptName($script)
    {
        $viewRenderer = $this->_getViewRenderer();

        // ensure script has proper suffix (extension)
        if (strpos($viewRenderer->getViewScriptPathSpec(), ':suffix') !== false) {
            $suffix = '.' . ltrim($viewRenderer->getViewSuffix(), '.');
            if (substr($script, -strlen($suffix)) !== $suffix) {
                $script .= $suffix;
            }
        }

        return $script;
    }

    /**
     * Get name of default module
     *
     * @return string
     */
    protected function _getDefaultModule()
    {
        return $this->_getViewRenderer()->getFrontController()->getDispatcher()->getDefaultModule();
    }

    /**
     * Get module directory
     *
     * @param string $module
     * @return string
     */
    protected function _getModuleDirectory($module)
    {
        $viewRenderer = $this->_getViewRenderer();

        $request = $viewRenderer->getRequest();

        $origModule = $request->getModuleName();
        $request->setModuleName($module);

        // getModuleDirectory() throws exception if module directory cannot
        // be determined
        $moduleDir = $viewRenderer->getModuleDirectory();

        // restore original module name
        $request->setModuleName($origModule);

        return $moduleDir;
    }

    /**
     * Get base path for module views
     *
     * @param string $module OPTIONAL
     * @return string
     */
    protected function _getModuleViewBasePath($module)
    {
        $moduleDir = $this->_getModuleDirectory($module);

        // base path is built without using inflector, as this method is
        // intended for inline template use only
        // (btw, this is how it should be done in Partial view helper,
        // not by hard-coding views/ subdirectory, nor by searching for
        // controller directory and taking dirname() of it)
        $viewBasePath = strtr(
            $this->_getViewRenderer()->getViewBasePathSpec(),
            array(
                ':moduleDir' => $moduleDir,
            )
        );

        return $viewBasePath;
    }

    /**
     * Get class prefix corresponding to given module name
     *
     * @param string $module
     * @return string
     */
    protected function _getModuleClassPrefix($module)
    {
        $prefix = preg_replace('/[^0-9A-Za-z]+/', ' ', $module);
        $prefix = trim($prefix);
        $prefix = ucwords($prefix);
        $prefix = str_replace(' ', '_', $prefix) . '_';
        return $prefix;
    }

    /**
     * Sets view state
     *
     * @param  Zend_View_Abstract $view
     * @param  array $viewState
     * @return void
     */
    protected function _setViewState(Zend_View_Abstract $view, array $viewState = null)
    {
        // set variables
        if (isset($viewState['vars'])) {
            $view->assign($viewState['vars']);
        }

        // set script paths
        if (isset($viewState['scriptPaths'])) {
            $view->setScriptPath(null);
            foreach ($viewState['scriptPaths'] as $path) {
                $view->addScriptPath($path);
            }
        }
    }
}
