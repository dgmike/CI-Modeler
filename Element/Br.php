<?php

class Modeler_Element_Br implements Modeler_Element
{
    public function __construct()
    {
    }

    public function render( Modeler_Formulator $formulator, array $values )
    {
        return '<br class="clear" />';
    }
}

