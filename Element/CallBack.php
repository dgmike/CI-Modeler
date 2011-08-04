<?php

class Modeler_Element_CallBack implements Modeler_Element
{
	public $html = '';

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Callback invalid!');
        }
        $this->callback = $callback;
    }

    public function render( Modeler_Formulator $formulator, array $values )
    {
        return call_user_func($this->callback, $formulator, $values);
    }
}
