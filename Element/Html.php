<?php

class Modeler_Element_Html implements Modeler_Element
{
	public $html = '';

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function render( Modeler_Formulator $formulator, array $values )
    {
        return $this->html;
    }
}
