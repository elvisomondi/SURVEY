<?php

function LS($class)
{
    $namespace = 'LS';
    $separator = '_';

    if (0 !== strpos($class, $namespace.$separator))
    {
        return;
    }

    $translated = str_replace($separator, DIRECTORY_SEPARATOR, $class);
    $libpath = dirname(__FILE__).DIRECTORY_SEPARATOR.'src';
    $file = $libpath.DIRECTORY_SEPARATOR.$translated.'.php';

    require $file; # provoke fatal error if file does not exists.
}
 
spl_autoload_register('LS');
