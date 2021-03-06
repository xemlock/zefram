<?php

/**
 * @category Zefram
 * @package  Zefram_Mail
 * @uses     Zend_Mail
 */
class Zefram_Mail extends Zend_Mail
{
    /**
     * Default Mail character set
     * @var string
     * @static
     */
    protected static $_defaultCharset = null;

    /**
     * Default encoding of Mail headers
     * @var string
     * @static
     */
    protected static $_defaultHeaderEncoding = null;

    /**
     * Last used transport
     */
    protected $_transport;

    /**
     * @param  string $charset OPTIONAL
     * @return void
     */
    public function __construct($charset = null)
    {
        if (null === $charset) {
            $charset = self::$_defaultCharset;
        }

        parent::__construct($charset);

        if (null !== self::getDefaultFrom()) {
            $this->setFromToDefaultFrom();
        }

        if (null !== self::$_defaultHeaderEncoding) {
            $this->setHeaderEncoding(self::$_defaultHeaderEncoding);
        }
    }

    public function setTransport($transport = null)
    {
        $this->_transport = $transport;
        return $this;
    }

    public function getTransport()
    {
        if (!$this->_transport) {
            if (!self::$_defaultTransport instanceof Zend_Mail_Transport_Abstract) {
                $transport = new Zend_Mail_Transport_Sendmail();
            } else {
                $transport = self::$_defaultTransport;
            }
            $this->_transport = $transport;
        }
        return $this->_transport;
    }

    public function send($transport = null)
    {
        if ($transport !== null) {
            $this->setTransport($transport);
        }
        return parent::send($this->_transport);
    }

    /**
     * Add recipient and To-header. Duplicated emails are not re-added.
     *
     * @param  string|array $email
     * @param  string $name
     * @return Zefram_Mail
     */
    public function addTo($email, $name = '')
    {
        if (!is_array($email)) {
            $email = array($name => $email);
        }

        foreach ($email as $name => $address) {
            if (isset($this->_to[$address])) {
                continue;
            }

            $this->_addRecipientAndHeader('To', $address, is_int($name) ? '' : $name);
            $this->_to[$address] = $address;
        }

        return $this;
    }

    /**
     * Return recipients' email addresses.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * Attach a file to this message.
     *
     * Options:
     *
     * @param  string $path
     * @param  array $options OPTIONAL
     * @return Zefram_Mime_FileStreamPart
     */
    public function attachFile($path, array $options = array())
    {
        if (isset($options['inline']) && $options['inline']) {
            $options['disposition'] = Zend_Mime::DISPOSITION_INLINE;
        }

        if (empty($options['disposition'])) {
            $options['disposition'] = Zend_Mime::DISPOSITION_ATTACHMENT;
        }

        if (empty($options['encoding'])) {
            $options['encoding'] = Zend_Mime::ENCODING_BASE64;
        }

        $part = new Zefram_Mime_FileStreamPart($path, $options);

        if (empty($part->type)) {
            $part->type = Zefram_File_MimeType_Data::detect($part->getPath());
        }

        $this->addAttachment($part);

        return $part;
    }

    /**
     * @param  string $charset
     * @return void
     */
    public static function setDefaultCharset($charset)
    {
        self::$_defaultCharset = (string) $charset;
    }

    /**
     * @return string
     */
    public static function getDefaultCharset()
    {
        return self::$_defaultCharset;
    }

    /**
     * @return void
     */
    public static function clearDefaultCharset()
    {
        self::$_defaultCharset = null;
    }

    /**
     * @param  string $headerEncoding
     * @return void
     */
    public static function setDefaultHeaderEncoding($headerEncoding)
    {
        self::$_defaultHeaderEncoding = (string) $headerEncoding;
    }

    /**
     * @return string
     */
    public static function getDefaultHeaderEncoding()
    {
        return self::$_defaultHeaderEncoding;
    }

    /** 
     * @return void
     */
    public static function clearDefaultHeaderEncoding()
    {
        self::$_defaultHeaderEncoding = null;
    }
}
