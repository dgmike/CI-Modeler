<?php

class Modeler
{
    public function __construct()
    {
        foreach (glob(dirname(__FILE__).DIRECTORY_SEPARATOR.'*.php') as $file) {
            require_once $file;
        }

        foreach (glob(dirname(__FILE__).DIRECTORY_SEPARATOR.'Element'.DIRECTORY_SEPARATOR.'*.php') as $file) {
            require_once $file;
        }
    }
}
