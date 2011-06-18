<?php

class Modeler_Result implements Iterator, ArrayAccess
{
    private $_valid = null;
    private $_count = null;
	private $_current = null;

	public $result = null;
	public $model = null;

    public function __construct(Modeler_ARecord $model, CI_DB_result $result)
    {
        $this->model = $model;
        $this->result = $result->result_array();
        $this->_count = count($this->result);
        $this->rewind();
    }
    
    /**
     * count - retorna a quantidade de linhas retornadas pelo banco de dados
     * 
     * @access public
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }
    
    // Iterator (permite fazer foreach com o objeto)

    /**
     * Retorna o iterator para a primeira posicao
     *
     * @return void
     */
    public function &rewind()
    {
        $this->_valid   = (reset($this->result) !== false); 
        $this->_current = current($this->result);
        return $this->current();
    }

    /**
     * Retorna o item atual
     *
     * @return mixed
     */
    public function &current()
    {
        if ($this->_current) {
            return $this;
        }
        return null;
    }
    
    /**
     * Move o ponteiro dos resultados para a proxima posicao
     *
     * @return void
     */
    public function &next()
    {
        $this->_valid = (next($this->result) !== false); 
        $this->_current = current($this->result);
        return $this->current();
    }

    /**
     * Verifica se o ponteiro atual eh valido
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_valid;
    }

    /**
     * Retorna a chave do item atual
     *
     * @return void
     */
    public function key()
    {
        return key($this->result);
    }
    // Fim do Iterator
    // Overload
    
    /**
     * Sobrescreve o get, pegando os valores de um determinado campo do 
     * item atual no objeto.
     *
     * @param string $key Item que está referenciando
     *
     * @return mixed
     */
    public function __get($key)
    {
        $elm &= $this->_current;
        if ('array' === gettype($elm)) {
            return (isset($elm[$key]) ? $elm[$key] : null);
        } elseif ('object' === gettype($elm)) {
            return (isset($elm->{$key}) ? $elm->{$key} : null);
        }
        return null;
    }

    /**
     * Disable __set function, use $model->save() to save your data
     * Thisclasswill be used only for read mode
     * 
     * @param string $key   Chave que está tentando trocar
     * @param string $value Valor novo da chave
     *
     * @return void
     */
    public function __set($key, $value)
    {
        trigger_error('This object is only for read, please use $CI->'
                      .get_class($this->model).'->save($data) to save.');
    }

    // Fim do overload
    // ArrayAccess (voce pode acessar este objeto como um array)

    /**
     * offsetExists Verifica se um elemento existe
     * 
     * @param string $offset offset you want to verify
     *
     * @access public
     * @return bool
     */
    public function offsetExists($offset)
    {
        $elm = $this->current();
        if ('array' === gettype($elm)) {
            return isset($elm[$offset]);
        } elseif ('object' === gettype($elm)) {
            return isset($elm->{$offset});
        }
    }

    /**
     * Disable __set function, use $model->save() to save your data
     * Thisclasswill be used only for read mode
     * 
     * @param string $offset key que voce deseja setar
     * @param string $value  valor que voce deseja setar
     *
     * @access public
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        trigger_error('This object is only for read, please use $CI->'
                      .get_class($this->model).'->save($data) to save.');
    }

    /**
     * Disable __set function, use $model->save() to save your data
     * Thisclasswill be used only for read mode
     * 
     * @param string $offset key que voce deseja setar
     *
     * @access public
     * @return void
     */
    public function offsetUnset($offset)
    {
        trigger_error('This object is only for read, please use $CI->'
                      .get_class($this->model).'->save($data) to save.');
    }

    /**
     * offsetGet - Pega um valor desejado
     * 
     * @param string $offset offset que deseja pegar
     *
     * @access public
     * @return void
     */
    public function offsetGet($offset)
    {
        $elm &= $this->_current;
        if ('array' === gettype($elm)) {
            return (isset($elm[$offset]) ? $elm[$offset] : null);
        } elseif ('object' === gettype($elm)) {
            return (isset($elm->{$offset}) ? $elm->{$offset} : null);
        }
        return null;
    }
    // Fim do ArrayAccess (voce pode acessar este objeto como um array)




    public function result_array()
    {
        return $this->result;
    }
 
    public function form()
    {
        $elements = func_get_args();
        if (!$elements) {
            $elements = array_keys($this->model->fields);
        }
        $form = new Modeler_Formulator($this->_current, $this->model);
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
