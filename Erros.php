<?php

class ARecord_Empty_Fields extends Exception
{
    public function __construct($message = NULL, $code = 1)
    {
        if (empty($message)) {
            $message = 'You need to especify the \'$fields\' OR define \'$autofields\' = true propriety of object.';
        }
        parent::__construct($message, $code);
    }
}

class ARecord_No_Primary extends Exception
{
    public function __construct($message = NULL, $code = 1)
    {
        if (empty($message)) {
            $message = 'You need to especify the \'$primary\' OR define \'$autofields\' = true propriety of object.';
        }
        parent::__construct($message, $code);
    }
}
