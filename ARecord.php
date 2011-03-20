<?php

class Modeler_ARecord
{
	public $db         = null;

	public $table      = null;

	public $autofields = false;

	public $fields     = array();
    public $primary    = null;
    public $keys       = null;
    public $forms      = array();

    public function __construct()
    {
        log_message('debug', "Model Class Initialized");
        $this->db   =& get_instance()->db;
        $class_name = strtolower(get_class($this));
        if (!$this->table) {
            $this->table = preg_replace('@^model_|_model$@', '', $class_name);
        }
        if (!$this->autofields && !$this->fields) {
            throw new ARecord_Empty_Fields;
        }
        if (!$this->autofields && $this->primary) {
            throw new ARecord_No_Primary;
        }
        if ($this->autofields) {
            $this->_auto_set_fields();
        }
        if (!$this->primary) {
            $this->_auto_set_keys();
        }
    }

    public function get($keys)
    {
        if (count($this->primary) > 1) {
            // @TODO colocar o get em formato de array
            return '@TODO';
        }
        return new Modeler_Result($this, $this->db
             ->from($this->table)
             ->where(array($this->primary[0] => $keys))
             ->get() );
    }

    private final function _auto_set_fields()
    {
        $cols = $this->db->query('DESCRIBE '.$this->table);
        foreach ($cols->result() as $col) {
            $values = null;
            $type = 'text';
            if (preg_match('@^int|^float@', strtolower($col->Type))) {
                $type = 'number';
            }
            if (preg_match('@^enum\s*\((.*)\s*\)$@', strtolower($col->Type), $matches)) {
                $extract_args = create_function('', 'return func_get_args();');
                $vals = eval('return $extract_args('.$matches[1].');');
                $type = 'select';
                if ($vals) {
                    $values = array();
                    foreach ($vals as $item) {
                        $values[$item] = $item;
                    }
                }
            }
            if (preg_match('@^text@', strtolower($col->Type))) {
                $type = 'textarea';
            }
            if (preg_match('@^bool@', strtolower($col->Type))) {
                $type = 'checkbox';
            }
            if ($col->Key) {
                $type = 'hidden';
            }
            $this->fields[$col->Field] = array_merge(array(
                    'label' => str_replace('_', ' ', $col->Field),
                    'type' => $type,
                    'rules' => '',
                    ), ( empty( $this->fields[$col->Field] ) ? array(  ) : $this->fields[$col->Field] ));
            if ($values !== null) {
                $this->fields[$col->Field]['values'] = $values;
            }
        }
    }

    private final function _auto_set_keys()
    {
        $result = $this->db->query('SHOW INDEX IN '.$this->table);
        foreach ($result->result() as $item) {
            $this->keys[] = $item->Column_name;
            if (strtolower($item->Key_name) == 'primary') {
                $this->primary[] = $item->Column_name;
            }
        }
        $this->keys = array_unique($this->keys);
        $this->primary = array_unique($this->primary);
    }

    public final function form()
    {
        $values   = array();
        $elements = func_get_args();
        if (func_num_args() && 'array' === gettype($elements[0])) {
            $values = array_shift($elements);
        }
        if (!$elements) {
            $elements = array_keys($this->fields);
        }
        $form = new Modeler_Formulator($this->fields, $values);
        $form->setElements($elements);
        return $form;
    }
}
