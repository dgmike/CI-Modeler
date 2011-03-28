<?php

class Modeler_Form
{
	public $form_pattern = null;

    public function __construct($form_pattern)
    {
        $this->form_pattern = $form_pattern;
    }

    public function getFormPattern()
    {
        return $this->form_pattern;
    }
}
