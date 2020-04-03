<?php

require __DIR__ . '/../vendor/autoload.php';

$validator = new Zefram_Validate_Hostname();
$validatorClass = new ReflectionClass($validator);

$validTldsProperty = $validatorClass->getProperty('_validTlds');
$validTldsProperty->setAccessible(true);
$validTlds = $validTldsProperty->getValue($validator);

$url = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt';

$lines = explode("\n", trim(file_get_contents($url)));
$lines = array_map('trim', $lines);

$version = ltrim(array_shift($lines), "# \t");

$INTL_IDNA_VARIANT = defined('INTL_IDNA_VARIANT_UTS46')
    ? INTL_IDNA_VARIANT_UTS46
    : INTL_IDNA_VARIANT_2003;

$tlds = array_map('strtolower', $lines);
foreach ($tlds as $tld) {
    if (substr($tld, 0, 4) === 'xn--') {
        $tlds[] = idn_to_utf8($tld, 0, $INTL_IDNA_VARIANT);
    }
}

if ($tlds === $validTlds) {
    echo 'TLD list is valid, no update required.', PHP_EOL;
    exit(0);
}


$template = <<<END
<?php

/**
 * Hostname validator which provides an updated list of TLDs.
 *
 * @category Zefram
 * @package  Zefram_Validate
 */
class Zefram_Validate_Hostname extends Zend_Validate_Hostname
{
    /**
     * Array of valid top-level-domains
     *
     * %version%
     *
     * @see %url%  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/  Official list of supported TLDs
     * @var string[]
     */
    protected \$_validTlds = array(
        %tlds%
    );
}

END;

function quote($str) {
    return "'$str'";
}

$result = strtr($template, array(
    '%url%'     => $url,
    '%version%' => $version,
    '%tlds%'    => implode(",\n        ", array_map('quote', $tlds)) . ',',
));

file_put_contents($validatorClass->getFileName(), $result);

echo 'Hostname validator TLD list updated.', PHP_EOL;
exit(0);
