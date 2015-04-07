<?php

class Zefram_Controller_Action_Helper_FlashMessenger
    extends Zend_Controller_Action_Helper_FlashMessenger
{
    /**
     * Check if specific namespace(s) contains messages.
     *
     * @param  string|array $namespace OPTIONAL
     * @return bool
     */
    public function hasMessages($namespace = null)
    {
        // handle null, otherwise the loop will not be entered
        if ($namespace === null) {
            $namespace = $this->getNamespace();
        }

        $namespaces = (array) $namespace;
        foreach ($namespaces as $namespace) {
            if (parent::hasMessages($namespace)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function addMessage($message, $namespace = null)
    {
        if (!is_string($namespace) || $namespace == '') {
            $namespace = $this->getNamespace();
        }

        $count = isset(self::$_session->{$namespace})
               ? count(self::$_session->{$namespace})
               : 0;

        // According to http://stackoverflow.com/questions/1086075/zend-framework-flashmessenger-problem
        // there are PHP 5.2.x versions, that have a problem with
        // Zend_Controller_Action_Helper_FlashMessenger::addMessage() (line 143):
        // self::$_session->{$this->_namespace}[] = $message;

        // line 143 issues the following notice:
        //   Notice: Indirect modification of overloaded property
        //   Zend_Session_Namespace::$default has no effect in
        //   Zend\Controller\Action\Helper\FlashMessenger.php on line 143
        @parent::addMessage($message, $namespace);

        if ($count == count(self::$_session->{$namespace})) {
            $messages = self::$_session->{$namespace};
            $messages[] = $message;
            self::$_session->{$namespace} = $messages;
        }

        return $this;
    }

    /**
     * Calls methods on flash messenger
     *
     * @param  string $method
     * @param  array $args
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        // Recognize has<Namespace>Messages() and get<Namespace>Messages() methods
        if (preg_match('/^(get|has)(\w+?)Messages$/i', $method, $matches)) {
            $namespace = $this->_getCallNamespace($matches[2]);
            $method = $matches[1] . 'Messages';
            return call_user_func(array($this, $method), $namespace);
        }

        // Recognize add<Namespace>Message() method
        if (preg_match('/^add(\w+?)Message$/i', $method, $matches)) {
            $namespace = $this->_getCallNamespace($matches[1]);
            return $this->addMessage(reset($args), $namespace);
        }

        throw new BadMethodCallException("Unrecognized method '$method()'");
    }

    /**
     * Transforms namespace contained within method name passed to __call()
     * to namespace actually stored in the session
     *
     * @param  string $namespace
     * @return string
     */
    protected function _getCallNamespace($namespace)
    {
        // this effectively performs lcfirst() on a given namespace, but since
        // the function is available since PHP 5.3.0 we cannot use it here
        return strtolower(substr($namespace, 0, 1)) . substr($namespace, 1);
    }
}
