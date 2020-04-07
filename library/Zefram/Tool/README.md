# Zefram_Tool

This is an extension to `Zend_Tool`.

## Motivations

I wanted to build a CLI tool based on [`Zend_Tool_Framework`](https://framework.zend.com/manual/1.12/en/zend.tool.framework.html),
but surprisingly it turned out that it needs some tweaking to become
a sufficiently general framework for building such tools.

The problem was that the string with command name (`zf`) was
hardcoded in many places (help responses, interactive prompt), also
there was no possibility to change the header in the help output.

## Usage

Almost like the original `Zend_Tool_Framework_Client_Console` class,
except that you can now provide a command name or a custom help
header:

```php
$console = new Zefram_Tool_Framework_Client_Console(array(
    'commandName'   => 'cli-tool',
    'helpHeader'    => array(
        array('CLI Tool', array('color' => array('hiWhite'), 'separator' => false)),
        ' Version ' . CLI_TOOL_VERSION,
    ),
    'classesToLoad' => '...',
));
$console->dispatch();
```

### Notes

Because of how the hardcoded `zf` command name is handled, to display
a `zf` string in the output one need to append content with `commandName`
decorator disabled:

```php
/** @var Zend_Tool_Framework_Client_Response $response */
$response->appendContent('zf', array('commandName' => false));
```
