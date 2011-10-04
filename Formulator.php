<?php

class Modeler_Formulator
{
    private $_reference = null;
    public  $fields     = array();
    public  $values     = array();
    public  $elements   = array();

    /**
     * __construct 
     * 
     * @param array $fields 
     * @param mixed $values 
     * @param mixed $reference 
     * @access public
     * @return void
     */
    public function __construct($values, $reference = null)
    {
        if ($reference) {
            $this->_reference = $reference;
            $this->fields = $reference->fields;
        }
        $this->values = $values;
    }

    /**
     * setElements 
     * 
     * @param array $elements 
     * @access public
     * @return void
     */
    public function setElements(array $elements = array())
    {
        $this->elements = $this->_validateElements($elements);
        $this->form     = $this->_parseElements($this->elements);
    }

    /**
     * show 
     * 
     * @param mixed $print 
     * @access public
     * @return void
     */
    public function show( $print = false )
    {
        if ( $print ) {
            echo $this->form;
            return;
        }
        return $this->form;
    }

    /**
     * __toString 
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        return empty($this->form) ? '' : $this->form;
    }

    /**
     * _validateElements 
     * 
     * @param array $elements 
     * @access private
     * @return void
     */
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
            } elseif ($item instanceof Modeler_Form) {
                $_elements[] = $item;
            } else {
                $ref = $this->_reference ? ': ' . get_class($this->_reference) : '.';
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

    public function parseElements(array $elements = array())
    {
        return $this->_parseElements($elements);
    }

    /**
     * _parseElements 
     * 
     * @param array $elements 
     * @access private
     * @return void
     */
    private function _parseElements(array $elements = array())
    {
        $valid = array_keys($this->fields);
        $form  = PHP_EOL;
        foreach ($elements as $item) {
            if (is_string($item) && in_array($item, $valid )) {
                $type = empty($this->fields[$item]['type']) ? 'text' : $this->fields[$item]['type'];
                $method = '_create'.ucfirst(strtolower( $type )).'Element';
                if ( is_callable(array( $this, $method ) ) ) {
                    $value = empty( $this->values[$item] ) ? '' : $this->values[$item];
                    $form .= $this->$method( $item, $this->fields[$item], $value );
                }
            } elseif (is_object($item) && $item instanceof Modeler_Form) {
                if (!in_array($item->getFormPattern(), array_keys($this->_reference->forms))) {
                    trigger_error('Element \''.$item->getFormPattern().'\' is not a valid element for this form', E_USER_NOTICE);
                } else {
                    $form .= $this->_parseModelerForm($item);
                }
            } elseif (is_object($item) && $item instanceof Modeler_Element) {
               $form .= $item->render($this, $this->values);
            } else {
                trigger_error('Element \''.(is_string($item) ? $item : get_class($item) . ' (object)').'\' is not a valid element for this form', E_USER_NOTICE);
            }
        }
        return $form;
    }

    private function _parseModelerForm(Modeler_Form $modeler_form)
    {
        $valid    = array_keys($this->fields);
        $elements = $this->_reference->forms[$modeler_form->getFormPattern()];
        return $this->_parseElements($elements);
    }

    /**
     * _formatClass 
     * 
     * @param mixed $field 
     * @access private
     * @return void
     */
    private function _formatClass($field)
    {
        return empty( $field['class'] ) ? '' : (' '.$field['class']);
    }

    /**
     * _formatLabel 
     * 
     * @param mixed $field 
     * @access private
     * @return void
     */
    private function _formatLabel($field)
    {
        if (!empty($this->_reference->noLabels)) {
            return '';
        }
        return empty( $field['label'] ) ? '' : ('  <span>'.$field['label'].'</span>' . PHP_EOL);
    }

    /**
     * _formatSmall 
     * 
     * @param mixed $field 
     * @access private
     * @return void
     */
    private function _formatSmall($field)
    {
        return empty( $field['small'] ) ? '' : ('  <small>'.$field['small'].'</small>' . PHP_EOL);
    }


    private function _labeler($type, $key, $field, $inside)
    {
        $label = '<label class="form_%s %s%s">%s %s</label>';
        $label = sprintf($label, $type, $key, $this->_formatClass($field), $this->_formatLabel($field), $inside);
        return $label;
    }

    /**
     * _createTextElement 
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createTextElement( $key, $field, $value )
    {
        $text_element = sprintf('<input type="text" name="%s" value="%s" /> %s', $key, $value, $this->_formatSmall($field) );
        $labeled = $this->_labeler('text', $key, $field, $text_element);
        return $labeled;
    }

    /**
     * _createNumberElement
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createNumberElement( $key, $field, $value )
    {
        $browser  = $_SERVER['HTTP_USER_AGENT'];
        if (false !== strpos($browser, 'WebKit')) {
            $type = 'number';
        } else {
            $type = 'text';
        }
        $template = '<label class="form_number %2$s%4$s">%7$s'
                  . '%5$s'
                  . '  <input type="%1$s" name="%2$s" value="%3$s" />%7$s'
                  . '%6$s</label>%7$s';
        return vsprintf(
            $template, array(
                /* 1 */ $type,
                /* 2 */ $key, 
                /* 3 */ $value,
                /* 4 */ $this->_formatClass( $field ),
                /* 5 */ $this->_formatLabel( $field ),
                /* 6 */ $this->_formatSmall($field),
                /* 7 */ PHP_EOL 
            )
        );
    }

    /**
     * _createEmailElement
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createEmailElement( $key, $field, $value )
    {
        $password_element = sprintf('<input type="email" name="%s" value="%s" /> %s', $key, $value, $this->_formatSmall($field) );
        $labeled = $this->_labeler('email', $key, $field, $password_element);
        return $labeled;
    }

    /**
     * _createTextareaElement 
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createTextareaElement( $key, $field, $value )
    {
        return sprintf( '<label class="form_text %s%s">' . PHP_EOL
                      . '%s'
                      . '  <textarea name="%s">%s</textarea>' . PHP_EOL
                      . '%s</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $value, $this->_formatSmall($field), PHP_EOL );
    }

    /**
     * _createHiddenElement 
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createHiddenElement( $key, $field, $value )
    {
        return sprintf( '<input type="hidden" name="%s" value="%s" />%s', $key, $value, PHP_EOL.PHP_EOL );
    }

    /**
     * _parseOptions 
     * 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _parseOptions($field, $value, $fields = null)
    {
        if (!$fields) {
            $fields = $field['values'];
        }
        $options = array();
        foreach ($fields as $key => $val) {
            if (gettype($val) == 'array') {
                list($k) = array_keys($val);
                $group_val = $val[$k];
                unset($val[$k]);

                $opts       = PHP_EOL.'   <optgroup label="'.$group_val.'">';
                $opts      .= $this->_parseOptions($field, $value, $val);
                $opts      .= PHP_EOL.'   </optgroup>';
                $options[]  = $opts;
            } else {
                $selected  = $key == $value ? ' selected="selected"' : '';
                $options[] = sprintf( PHP_EOL . '    <option value="%s"%s>%s</option>', $key, $selected, $val );
            }
        }
        return implode( '', $options );
    }

    /**
     * _createSelectElement 
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createSelectElement( $key, $field, $value )
    {
        $options = $this->_parseOptions( $field, $value );
        return sprintf( '<label class="form_text %s%s">' . PHP_EOL
                      . '%s'
                      . '  <select name="%s">%s' . PHP_EOL
                      . '  </select>' . PHP_EOL
                      . '%s</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $options, $this->_formatSmall($field), PHP_EOL );
    }

    /**
     * _createPasswordElement 
     * 
     * @param mixed $key 
     * @param mixed $field 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function _createPasswordElement( $key, $field, $value )
    {
        if (!empty($field['novalue'])) {
            $value = '';
        }
        $render = sprintf( '<label class="form_password %s%s">' . PHP_EOL
                . '%s'
                . '  <input type="password" name="%s" value="%s" />' . PHP_EOL
                . '%s</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $value, $this->_formatSmall($field), PHP_EOL );
        if (!empty($field['label2'])) {
            $field['label'] = $field['label2'];
            $field['small'] = empty($field['small2']) ? '' : $field['small2'];
            $render .= sprintf( '<label class="form_password %s2%s">' . PHP_EOL
                    . '%s'
                    . '  <input type="password" name="%s2" value="%s" />' . PHP_EOL
                    . '%s</label>%s', $key, $this->_formatClass( $field ), $this->_formatLabel( $field ), $key, $value, $this->_formatSmall($field), PHP_EOL );
        }

        return $render;
    }

    private function _createDatetimeElement($key, $field, $value)
    {
        $value = str_replace(' ', 'T', $value);
        $value = preg_replace('@:\d\d$@', '', $value);
        $datetime_element = sprintf('<input type="datetime-local" name="%s" value="%s" /> %s', $key, $value, $this->_formatSmall($field) );
        $labeled = $this->_labeler('datetime', $key, $field, $datetime_element);
        return $labeled;
    }

    private function _createDateElement($key, $field, $value)
    {
        $datetime_element = sprintf('<input type="date" name="%s" value="%s" /> %s', $key, $value, $this->_formatSmall($field) );
        $labeled = $this->_labeler('datetime', $key, $field, $datetime_element);
        return $labeled;
    }
}
