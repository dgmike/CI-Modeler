<?php
/**
 * Modeler Active Record
 * 
 * Esta classe serve para ser extendida pelo seu Model e assim ganhar vários
 * recursos para facilitar a vida do programador. Um bom exemplo é poder
 * usar um método para gerar formulários automáticamente, sem precisar escrever
 * uma únia linha de HTML.
 * 
 * @package Library
 */
class Modeler_ARecord
{
	/* Used in old CodeIgniter */
	private $_parent_name = '';

	public $ci         = null;
	public $db         = null;

	public $table      = null;

	public $autofields = false;

	public $fields     = array();
    public $primary    = null;
    public $keys       = null;
    public $forms      = array();

	/**
	 * Assign Libraries
	 *
	 * Used in old CodeIgniter
	 *
	 * Creates local references to all currently instantiated objects
	 * so that any syntax that can be legally used in a controller
	 * can be used within models.  
	 *
	 * @access private
	 */	
	function _assign_libraries($use_reference = TRUE)
	{
		$CI =& get_instance();				
		foreach (array_keys(get_object_vars($CI)) as $key)
		{
			if ( ! isset($this->$key) AND $key != $this->_parent_name)
			{			
				// In some cases using references can cause
				// problems so we'll conditionally use them
				if ($use_reference == TRUE)
				{
					$this->$key = NULL; // Needed to prevent reference errors with some configurations
					$this->$key =& $CI->$key;
				}
				else
				{
					$this->$key = $CI->$key;
				}
			}
		}		
	}

    /**
     * __construct 
     * 
     * Inicializa a classe, invista nas variáveis públicas deste objeto para
     * manipular o seu __construct
     *
     * @access public
     */
    public function __construct()
    {
        log_message('debug', "Model Class Initialized");
        $this->ci   =& get_instance();
        $this->db   =& $this->ci->db;
        $class_name = strtolower(get_class($this));
        if (!$this->table) {
            $this->table = preg_replace('@^model_|_model$@', '', $class_name);
            log_message('debug', 'Modeler_ARecord: discovering table name: ' . $this->table);
        }
        if (!$this->autofields && !$this->fields) {
            throw new ARecord_Empty_Fields;
        }
        if (!$this->autofields && !$this->primary) {
            throw new ARecord_No_Primary;
        }
        if ($this->autofields) {
            log_message('debug', 'Modeler_ARecord: setting fields automaticaly');
            $this->_auto_set_fields();
        }
        if (!$this->primary) {
            log_message('debug', 'Modeler_ARecord: setting keys automaticaly');
            $this->_auto_set_keys();
        }
        $this->forms = $this->setForms();
    }

    public function setForms()
    {
        return array('default' => array_keys($this->fields));
    }

    /**
     * Auto Set Fields
     * 
     * Quando você não seta os campos na model este método é chamado para setar
     * os campos com base nos dados encontrados no DESCRIBE do banco de dados.
     * Atenção: Valido apenas para bancos MySQL
     * 
     * @access private
     */
    private final function _auto_set_fields()
    {
        $cols = $this->db->query('DESCRIBE '.$this->table);
        foreach ($cols->result() as $col) {
            $values = null;
            $type = 'text';
            if (preg_match('@^int|^float@', strtolower($col->Type))) {
                $type = 'number';
            }
            $regexp = '@^enum\s*\((.*)\s*\)$@';
            if (preg_match($regexp, strtolower($col->Type), $matches)) {
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
            $default_fields = ( empty( $this->fields[$col->Field] ) ? 
                                array(  ) : $this->fields[$col->Field] );
            $this->fields[$col->Field] = array_merge(array(
                    'label' => str_replace('_', ' ', $col->Field),
                    'small' => '',
                    'type' => $type,
                    'rules' => '',
                    ), $default_fields);
            if ($values !== null) {
                $this->fields[$col->Field]['values'] = $values;
            }
        }
    }

    /**
     * Auto Set Keys
     * 
     * Caso você não sete os valores para $keys na sua model este método é
     * executado automáticamente para setar os dados com base no SHOW INDEX
     * do banco de dados.
     * Este método é valido apenas para MySQL.
     * 
     * @access private
     * @return void
     */
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

    /**
     * Retorna um resultado com base em suas chaves
     * 
     * @param array $keys Valor(es) que você pretende usar como where
     * @return Modeler_Result
     */
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

    /**
     * Gera um formulário para poder usar como achar mais conveniente
     * 
     * @param array optional Passe um array associativo com os values dos campos
     * @param string optional Passe os campos. Uma string para cada campo
     * @return Modeler_Formulator
     */
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
        $form = new Modeler_Formulator($values, $this);
        $form->setElements($elements);
        return $form;
    }

    /**
     * Executa ou appenda a validação dos campos enviados para o sistema
     *
     * @param bool  $run    Executar o script de validação imediatamente?
     * @param array $campos Um array dos campos que gostaria de validar
     *
     * @return bool
     */
    function validar($run=true, $campos = array()) {
        $this->ci->load->library('form_validation');
        $fields = $this->fields;
        if (!$campos) {
            $campos = array_keys($fields);
        }
        foreach ($fields as $key=>$field) {
            if (in_array($key, $campos)) {
                if (!empty($field['label'])) {
                    $label = $field['label'];
                } elseif (!empty($field['small'])) {
                    $label = $field['small'];
                } else {
                    $label = $key;
                }
                $this->ci
                    ->form_validation
                    ->set_rules($key, $label, $field['rules']);
            }
        }
        if (!$run) return true;
        return $this->ci->form_validation->run();
    }

    /**
     * Export Fields
     * 
     * Ajuda a gerar os campos para sua model. Este método deve ser chamado
     * sem nenhum parametro, apesar de ele usar dois parametros, usados por
     * ele mesmo para fazer os loops
     */
    public function export_fields($preppend = '', $data = null)
    {
        if (!func_num_args()) {
            print   '<pre class="debug" style="text-align:left;'
                  . 'background:#FFFFFF;color:#333333;padding:5px;">' . PHP_EOL;
            $fields = 'public $fields = ' . $this->fields;
        } else {
            $fields = $data;
        }
        print 'array(' . PHP_EOL;
        foreach ($fields as $k=>$v) {
            if ('array' === gettype($v)) {
                print $preppend . '    \'' . $k . '\' => ';
                $this->export_fields($preppend . '    ', $v);
            } else {
                print   $preppend . '    \'' . $k . '\' => \'' . addslashes($v)
                      . '\',' . PHP_EOL;
            }
        }
        print $preppend . ')' . (!$preppend ? ';' : ',') . PHP_EOL;
        if (!func_num_args()) {
            print '</pre>';
        }
    }
}
