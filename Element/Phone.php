<?php

class Modeler_Element_Phone implements Modeler_Element
{
    public function render(Modeler_Formulator $formulator, array $values)
    {
        $fieldset  = '<fieldset class="telefone"><legend>Telefones</legend>';
        if (!empty($values['telefone'])) {
            $fieldset .= $this->renderValues($values['telefone']);
        }
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

    public function renderValues(array $values)
    {
        $fields = '';
        foreach ($values as $key=>$item) {
            $fields .= '

                <label id="id_'.$key.'" class="first" style="display: block;">
                    <span class="input-form-telefone">
                        <input type="hidden" name="telefone[id_'.str_replace('id_', '', $key).'][tipo]" value="'.$item['tipo'].'" />
                        <input type="hidden" name="telefone[id_'.str_replace('id_', '', $key).'][ddd]" value="'.$item['ddd'].'" />
                        <input type="hidden" name="telefone[id_'.str_replace('id_', '', $key).'][telefone]" value="'.$item['telefone'].'" />
                        <span class="text_tel">('.$item['ddd'].') '.$item['telefone'].'</span> 
                        <button type="button" class="action edit ui-state-highlight">editar</button>
                        <button type="button" class="action delete ui-state-error">remover</button>
                    </span>
                </label>

            ';
        }
        return $fields;
    }
}
