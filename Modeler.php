<?php

class Modeler
{
    public function __construct()
    {
        foreach (glob(dirname(__FILE__).DIRECTORY_SEPARATOR.'*') as $file) {
            require_once $file;
        }
    }
}
