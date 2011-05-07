<?php

interface Modeler_Element
{
    // public function __construct();

    public function render( Modeler_Formulator $formulator, array $values );
}
