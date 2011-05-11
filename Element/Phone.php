<?php

class Modeler_Element_Phone implements Modeler_Element
{
    public function render(Modeler_Formulator $formulator, array $values)
    {
        $fieldset  = '<fieldset class="telefone"><legend>Telefones</legend>';
        $fieldset .= '<div class="telefone-wrap">';
        $fieldset .= '
                <div class="telefone-wrap">
                    <label class="first x-tiny tipo">
                        <select name="">
                            <option value="residencial">Residencial</option>
                            <option value="comercial">Comercial</option>
                            <option value="celular">Celular</option>
                        </select>
                        <small>Tipo</small>
                    </label>
                    <label class="x-tiny ddd">
                        <input type="text" class="text" value="" name="" />
                        <small>DDD</small>
                    </label>
                    <label class="tiny numero">
                        <input type="text" class="text" value="" name="" />
                        <small>Número</small>
                    </label>
                    <label class="x-tiny">
                        <button class="button adicionar" type="button">Adicionar</button>
                    </label>
                </div>
        ';
        $fieldset .= '</div>';
        $fieldset .= '</fieldset>';
        return $fieldset;




    }
}
