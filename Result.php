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
        $form = new Modeler_Formulator($this->current, $this->model);
        $form->setElements($elements);
        return $form;
    }

    static function formatResult(array $result)
    {
        $return = array();
        foreach ($result as $key => $value) {
            if (strpos($key, '.') !== FALSE) {
                $key = strtok($key, '.');
                $rest = strtok(false);
                if (empty($return[$key])) {
                    $return[$key] = self::formatResult(array($rest => $value));
                } else {
                    $return[$key] = array_merge_recursive($return[$key], self::formatResult(array($rest => $value)));
                }
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }
}
