<?php

class Modeler_Element_Fieldset implements Modeler_Element
{
    public function __construct( $legend, $elements, array $config = array() )
    {
        $this->legend   = $legend;
        $this->elements = $elements;
        $this->config   = $config;
    }

    public function render( Modeler_Formulator $formulator, array $values)
    {
        $this->formulator = $formulator;
        $fieldset = '<fieldset class="fieldset%s"%s><legend>%s</legend>%s</fieldset>';
        return sprintf(
            $fieldset,
            empty($this->config['class']) ? '' : ' ' . $this->config['class'],
            empty($this->config['extra']) ? '' : ' ' . $this->config['extra'],
            $this->legend, $this->renderForm()
        );
    }

    public function renderForm()
    {
        return $this->formulator->parseElements($this->elements);
    }
}
