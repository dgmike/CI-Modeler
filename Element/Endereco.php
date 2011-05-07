<?php

class Modeler_Element_Endereco implements Modeler_Element
{
    public function __construct()
    {

    }

    public function render( Modeler_Formulator $formulator, array $values )
    {
        $fieldset  = '<fieldset class="fieldset"><legend>Endereço</legend>';
        $fieldset .= $formulator->parseElements(array('cep'));
        $fieldset .= '<label class="tiny nolabel"><input type="button" class="button verificarCep" value="Verificar" />';
        $fieldset .= ' <a href="http://www.buscacep.correios.com.br" target="_blank">Correios</a></label>';
        $fieldset .= $formulator->parseElements(array('endereco', 'numero', 'complemento', 'bairro', 'estado', 'cidade'));
        $fieldset .= '</fieldset>';
        return $fieldset;
    }
}
