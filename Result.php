<?php

class Modeler_Result
{
	public $result = null;
	public $model = null;
	public $current = null;

    public function __construct(Modeler_ARecord $model, CI_DB_result $result)
    {
        $this->model = $model;
        $this->result = $result->result_array();
        $this->current = reset($this->result);
    }

    public function form()
    {
        $elements = func_get_args();
        if (!$elements) {
            $elements = array_keys($this->model->fields);
        }
        $form = new Modeler_Formulator($this->model->fields, $this->current);
        $form->setElements($elements);
        return $form;
    }
}
