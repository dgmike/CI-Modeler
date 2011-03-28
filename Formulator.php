<?php

class Modeler_Formulator
{
    private $_reference = null;
    public  $fields     = array();
    public  $values     = array();
    public  $elements   = array();

    public function __construct(array $fields, $values, $reference = null)
    {
        if ($reference) {
            $this->_reference = $reference;
        }
        $this->fields = $fields;
        $this->values = $values;
    }

    public function setElements(array $elements = array())
    {
        $this->elements = $this->_validateElements($elements);
        $this->form     = $this->_parseElements($this->elements);
    }

    public function show( $print = false )
    {
        if ( $print ) {
            echo $this->form;
            return;
        }
        return $this->form;
    }

    public function __toString()
    {
        return $this->form;
    }

    private function _validateElements(array $elements = array())
    {
        $valid     = array_keys($this->fields);
        $_elements = array();
        foreach ($elements as $k => $item) {
            if ('array' === gettype($item)) {
                $_elements[] = $this->_validateElements($item);
                continue;
            }
            if ( in_array($item, $valid) ) {
                $_elements[] = $item;
            } else {
                $ref = $this->_reference ? ": {$this->_reference}" : '.';
                if (is_scalar($item)) {
                    $item_str = $item;
                } else {
                    $item_str = get_class($item);
                }
                trigger_error('Element \''.$item_str.'\' is not a valid element for this form'.$ref, E_USER_NOTICE);
            }
        }
        return $_elements;
    }

    private function _parseElements(array $elements = array())
    {
        $valid = array_keys($this->fields);
        $form  = PHP_EOL;
        foreach ($elements as $k=>$item) {
            if ( in_array($item, $valid )) {
                $type = empty($this->fields[$item]['type']) ? 'text' : $this->fields[$item]['type'];
                $method = '_create'.ucfirst(strtolower( $type )).'Element';
                if ( is_callable(array( $this, $method ) ) ) {
                    $value = empty( $this->values[$item] ) ? '' : $this->values[$item];
                    $form .= $this->$method( $item, $this->fields[$item], $value );
                }
            }
        }
        return $form;
    }

    private function _formatClass($field)
    {
        return empty( $field['class'] ) ? '' : (' '.$field['class']);
    }

    private function _formatLabel($field)
    {
        return empty( $field['label'] ) ? '' : ('  <span>'.$field['label'].'</span>' . PHP_EOL);
    }

    private function _createTextElement( $key, $field, $value )
    {
        return sprintf( '<label class="form_text %s%s">' . PHP_EOL
                      . '%s'
                      . '  <input type="text" name="%s" value="%s" />' . PHP_EOL
                      . '</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $value, PHP_EOL );
    }

    private function _createTextareaElement( $key, $field, $value )
    {
        return sprintf( '<label class="form_text %s%s">' . PHP_EOL
                      . '%s'
                      . '  <textarea name="%s">%s</textarea>' . PHP_EOL
                      . '</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $value, PHP_EOL );
    }

    private function _createHiddenElement( $key, $field, $value )
    {
        return sprintf( '<input type="hidden" name="%s" value="%s" />%s', $key, $value, PHP_EOL.PHP_EOL );
    }

    private function _parseOptions($field, $value)
    {
        $options = array(  );
        foreach ($field['values'] as $key => $val) {
            $selected  = $val == $value ? ' selected="selected"' : '';
            $options[] = sprintf( PHP_EOL . '    <option value="%s"%s>%s</option>', $key, $selected, $val );
        }
        return implode( '', $options );
    }

    private function _createSelectElement( $key, $field, $value )
    {
        $options = $this->_parseOptions( $field, $value );
        return sprintf( '<label class="form_text %s%s">' . PHP_EOL
                      . '%s'
                      . '  <select name="%s">%s' . PHP_EOL
                      . '  </select>' . PHP_EOL
                      . '</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $options, PHP_EOL );
    }
}
