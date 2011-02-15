<?php

class Modeler
{
    public function __construct()
    {
        get_instance()->load->library('model');
        foreach (glob(dirname(__FILE__).DIRECTORY_SEPARATOR.'*') as $file) {
            require_once $file;
        }
    }
}
