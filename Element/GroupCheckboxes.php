<?php

class Modeler_Element_GroupCheckboxes implements Modeler_Element
{
	public $element = '';

    public function __construct($element, $splitter = ';')
    {
        $this->element  = $element;
        $this->splitter = $splitter;
    }

    public function render( Modeler_Formulator $formulator, array $values )
    {
        $element = $formulator->fields[$this->element];
        $values  = $values[$this->element];
        if ($this->splitter == 'unserialize') {
            $values = unserialize($values);
        } elseif ($this->splitter == 'json_decode') {
            $values = json_decode($values);
        }
        if (empty($values[$this->element])) {
            $values[$this->element] = array();
        }
        $small = (empty($element['small']) ? '' : '<p><small>'.$element['small'].'</small></p>');
        if (!is_array($values)) {
            $values = explode($this->splitter, $values);
        }
        if (!in_array('values', array_keys($element))) {
            throw new ARecord_No_Values($this->element);
        }
        $checkboxes = array();
        foreach ($element['values'] as $key => $value) {
            $checkboxes[] = '
            <label class="checkbox '.$this->element.' '.$key.'">
                <input type="checkbox" name="'.$this->element.'[]" value="'.$key.'" '.(in_array($key, $values) ? 'checked="checked" ' : '').'/> 
                <span>' . $value . '</span>
            </label>
            ';
        }

        $checkboxes = implode(PHP_EOL, $checkboxes);
        return sprintf('<fieldset>
            <legend>%s</legend>
            %s
            %s
        </fieldset>', $element['label'], $small, $checkboxes);
    }
}

