<?php

abstract class Zefram_Os
{
    public static function normalizePath($path) // {{{
    {
        $parts = preg_split('/[\\\\\/][\\\\\/]*/', $path);
        $normalized = array();

        while ($parts) {
            $part = array_shift($parts);

            switch ($part) {
                case '..':
                    $atroot = empty($normalized)
                              || (1 == count($normalized) && ($normalized[0] == '' || substr($normalized[0], -1) == ':'));
                    if (!$atroot) {
                        array_pop($normalized);
                    }
                    break;

                case '.':
                    break;

                case '':
                    if (empty($normalized)) {
                        array_push($normalized, '');
                    }
                    break;

                default:
                    array_push($normalized, $part);
                    break;
            }
        }

        return implode('/', $normalized);
    } // }}}

    public static function pathLookup($filename, $path = null)
    {
        if (null === $path) {
            $path = getenv('PATH');
        }
        $dirs = explode(PATH_SEPARATOR, $path);
        array_unshift($dirs, getcwd());
        foreach ($dirs as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($path)) {
                return $path;
            }
        }
        return false;
    }

    public static function isWindows() // {{{
    {
        static $_isWindows = null;
        if (null === $_isWindows) {
            $_isWindows = in_array(
                strtoupper(PHP_OS), array('WIN32', 'WINNT', 'WINDOWS'), true
            );
        }
        return $_isWindows;
    } // }}}

    public static function exec($exec, $args = null) // {{{
    {
        if (is_array($args)) {
            // TODO
        }

        // From php.net forum:
        // In Windows, exec() issues an internal call to "cmd /c your_command".
        // This implies that your command must follow the rules imposed by
        // cmd.exe which includes an extra set of quotes around the full
        // command (see: http://ss64.com/nt/cmd.html).
        // Current PHP versions take this into account and add the quotes
        // automatically, but old versions don't. Apparently, the change
        // was made in PHP/5.3.0 yet not backported to 5.2.x because it's
        // a backwards incompatible change.
        if (self::isWindows()) {
            $ext = substr(strrchr(basename($exec), '.'), 1);
            if (0 == strlen($ext)) {
                $exec .= '.exe'; // is this really necessary?
            }
            $exec = escapeshellarg($exec);
            if (version_compare(PHP_VERSION, '5.3.0') < 0 && !strncmp($exec, '"', 1)) {
                $command = "\"$exec $args\"";
            } else {
                $command = "$exec $args";
            }
        } else {
            $command = "$exec $args";
        }
        return shell_exec($command);
    } // }}}

    public static function setEnv($key, $value) // {{{
    {
        // putenv/getenv and $_ENV are completely distinct environment stores
        $_ENV[$key] = $value;
        putenv("$key=$value");
    } // }}}

    // Wanna have setTempDir()? I'll tell you why you don't.
    //
    // main/php_open_temporary_file.c: php_get_temporary_directory(void)
    // once called its results are cached
    // on Windows TMP and TEMP variables are checked (in this order)
    // on UNIX systems TMPDIR is checked
    // and if that fails fall back to /tmp
    //
    // php_open_temporary_{fd,fd_ex,file}()functions also use
    // php_get_temporary_directory()
    //
    // $tmp=realpath('/temporary');putenv('TMPDIR='.$tmp);$_ENV['TMPDIR']=$tmp;echo sys_get_temp_dir();
    // the result of this code depends on whether sys_get_temp_dir() was called earlier.
    // If so, setting environment variables does not change anything

    // It is advisable to not use sys_get_temp_dir() as it cannot be
    // customized on a shared environment, use this instead
    //
    // When finding out that file has been uploaded, PHP internally determines
    // the location of temp dir, which effectively makes any methods of
    // setting temp dir during runtime (such as via environment) useless
    // and unreliable.
    //
    // It is then discouraged to use setEnv() for changing temp dir location.
    // Also session save handler (ext/session/mod_files.c) calls
    // php_get_temporary_directory is save path is not provided
    //
    // Temp dir should be treated as a read-only property of a system PHP runs
    // in.

    /**
     * @return string|false
     */
    public static function getTempDir() // {{{
    {
        $tmpdir = array();

        // sys_get_temp_dir() may be disabled for security reasons
        // requires PHP 5.2.1
        if (is_callable('sys_get_temp_dir')) {
            $tmpdir[sys_get_temp_dir()] = true;
        }

        foreach (array('TMP', 'TEMP', 'TMPDIR') as $var) {
            $dir = realpath(getenv($var));
            if ($dir) {
                $tmpdir[$dir] = true;
            }
        }

        $tmpdir['/tmp'] = true;
        $tmpdir['\\temp'] = true;

        foreach (array_keys($tmpdir) as $dir) {
            if (is_dir($dir) && is_writable($dir)) {
                return $dir;
            }
        }

        $tempfile = tempnam(md5(uniqid(rand(), true)), '');
        if ($tempfile) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }

        return false;
    } // }}}
}
