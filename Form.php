<?php
/**
 * Modeler to create forms
 *
 * PHP Version: 5.2
 *
 * @category Modeler_Form
 * @package  Ci_Modeler
 * @author   Michael <michael@pontovermelho.com.br>
 * @license  http://creativecommons.org/licenses/by-nd/3.0/br/ Atribuição-Vedada a criação de obras derivativas 3.0 Brasil (CC BY-ND 3.0)
 * @link     https://github.com/dgmike/CI-Modeler
 */
 
/**
 * Modeler_Form 
 * 
 * @category Modeler_Form
 * @package  Ci_Modeler
 * @author   Michael <michael@pontovermelho.com.br> 
 * @license  http://creativecommons.org/licenses/by-nd/3.0/br/ Atribuição-Vedada a criação de obras derivativas 3.0 Brasil (CC BY-ND 3.0)
 * @version  Release: 1.0
 * @link     https://github.com/dgmike/CI-Modeler/blob/master/Form.php
 */
class Modeler_Form
{
    public $form_pattern = null;

    /**
     * __construct 
     * 
     * @param array $form_pattern Form data type
     *
     * @return void
     */
    public function __construct($form_pattern)
    {
        $this->form_pattern = $form_pattern;
    }

    /**
     * getFormPattern 
     * 
     * @return void
     */
    public function getFormPattern()
    {
        return $this->form_pattern;
    }
}
