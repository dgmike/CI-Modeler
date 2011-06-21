<?php

class Modeler_Crudder extends CI_Controller
{
    public $model  = null;
    public $cmodel = null;
    public $base_url = '/';
    public $table_config = array(
        'row_start' => '<tr class="odd">',
        'row_alt_start' => '<tr class="even">',
    );

    public function index($page = 1, $per_page = 10)
    {
        $this->load->model($this->model, 'cmodel');
        $this->load->library('table');
        $filter = array();
        if (method_exists($this, '_filter')) {
            $filter = $this->_filter();
        }
        // @TODO fazer funcionar com multichaves
        $this->db->order_by($this->cmodel->primary[0], 'DESC');
        list($result, $total) = $this->cmodel->getPage($page, $filter, $per_page);

        if ($total) {
            // heading
            $heading = array_keys($this->cmodel->fields);
            if (method_exists($this, '_heading')) {
                $heading = $this->_heading();
            }
            $this->table->set_heading($heading);
            // result
            foreach ($result as $item) {
                $line = $result->toArray();
                if (method_exists($this, '_each')) {
                    $line = $this->_each($line);
                }
                call_user_func_array(array($this->table, 'add_row'), $line);
            }
            if ($this->table_config) {
                $this->table->set_template($this->table_config);
            }
            // generate
            $table = $this->table->generate();
            // pagination
            if (method_exists($this, '_pagination')) {
                $paginate = $this->_pagination($total, $page, $per_page);
            }
        } else {
            $table = '';
        }
        if (method_exists($this, '_actions')) {
            $actions = $this->_actions();
        }
        $title = $this->title;
        $message = $this->session->flashdata('message');
        $data = compact('table', 'paginate', 'actions', 'title', 'message');
        $this->_render('index', $data);
    }

    private function _pagination($total, $page, $per_page)
    {
        $pages = ceil( $total / $per_page );
        if ($pages < 2) {
            return false;
        }
        $pagination = array();
        // if ($page > 1) {
            $pagination[] = array(1, 'First', $page <= 1 ? 'disabled' : false);
            $pagination[] = array($page - 1, 'Previous', $page <= 1 ? 'disabled' : false);
        // }

        $range = range($page - 5, $page + 5);

        foreach ($range as $item) {
            if ($item < 1 || $item > $pages) {
                continue;
            }
            $pagination[] = array($item, $item, ($page == $item ? 'actual' : false));
        }
        // if ($page < $pages) {
            $pagination[] = array($page+1, 'Next', $page >= $pages ? 'disabled' : false);
            $pagination[] = array($pages, 'Last', $page >= $pages ? 'disabled' : false);
        // }
        foreach ($pagination as $key => $value) {
            if (empty($value[2])) {
                $pagination[$key] = sprintf('<a href="%s/index/%d/%d" title="">%s</a>', $this->base_url, $value[0], $per_page, $value[1]);
            // } elseif ($value[2] == 'actual') {
            //     $pagination[$key] = sprintf('<span class="actual">%s</span>', $value[1]);
            } else {
                $pagination[$key] = sprintf('<span class="%s">%s</span>', $value[2], $value[1]);
            }
        }
        return '<div class="pagination">' . implode(' ', $pagination) . '</div>';
    }

    public function _render($template, $data)
    {
        $this->load->view( 'crud/' . $template, $data);
    }

    public function edit($id)
    {
        $this->load->model($this->model, 'cmodel');
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            if ($this->cmodel->validar()) {
                foreach (array_keys($this->cmodel->fields) as $item) {
                    if (in_array($item, $this->cmodel->primary) || !isset($_POST[$item])) {
                        continue;
                    }
                    $data[$item] = $this->input->post($item);
                }
                if (method_exists($this, '_filter_data')) {
                    $data = $this->_filter_data($data);
                }
                $this->cmodel->update($data, array('id' => $id));
                $this->session->set_flashdata('message', array(
                    'type' => 'success', 
                    'text' => 'Dados salvos com sucesso!'
                ));
            } else {
                $this->session->set_flashdata('message', array(
                    'type' => 'error',
                    'title' => 'Ocorreram problemas ao salvar os dados, por favor verifique-os e tente novamente.',
                    'text' => validation_errors(),
                ));
            }
            redirect($this->base_url.'/edit/'.$id, 303);
            die;
        }
        // $this->db->select('id, nome, login, email');
        $result = $this->cmodel->get($id);
        $types =array_keys($this->cmodel->forms); 
        if (in_array('edit', $types)) {
            $form = $result->form(new Modeler_Form('edit'));
        } elseif (in_array('default', $types)) {
            $form = $result->form(new Modeler_Form('default'));
        } else {
            $form   = $result->form();
        }
        if (method_exists($this, '_editActions')) {
            $edit_actions = $this->_editActions($result);
        }
        $title = $this->title . ': edit #' . $id;
        $message = $this->session->flashdata('message');
        $data = compact('form', 'title', 'message', 'edit_actions');

        $this->_render('edit', $data);
    }

    public function add()
    {
        $this->load->model($this->model, 'cmodel');
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            if ($this->cmodel->validar()) {
                foreach (array_keys($this->cmodel->fields) as $item) {
                    if (in_array($item, $this->cmodel->primary) || !isset($_POST[$item])) {
                        continue;
                    }
                    $data[$item] = $this->input->post($item);
                }
                if (method_exists($this, '_filter_data')) {
                    $data = $this->_filter_data($data);
                }
                $id = $this->cmodel->insert($data);
                $this->session->set_flashdata('message', array(
                    'type' => 'success', 
                    'text' => 'Dados salvos com sucesso!'
                ));
                redirect($this->base_url.'/edit/' . $id, 303);
                die;
            } else {
                $this->session->set_flashdata('message', array(
                    'type' => 'error',
                    'title' => 'Ocorreram problemas ao salvar os dados, por favor verifique-os e tente novamente.',
                    'text' => validation_errors(),
                ));
            }
            redirect($this->base_url.'/add', 303);
            die;
        }
        $types = array_keys($this->cmodel->forms); 
        if (in_array('add', $types)) {
            $form = $this->cmodel->form(new Modeler_Form('add'));
        } elseif (in_array('edit', $types)) {
            $form = $this->cmodel->form(new Modeler_Form('edit'));
        } elseif (in_array('default', $types)) {
            $form = $this->cmodel->form(new Modeler_Form('default'));
        } else {
            $form   = $this->cmodel->form();
        }
        $title = $this->title . ': add';
        $message = $this->session->flashdata('message');
        $data = compact('form', 'title', 'message');
        $this->_render('add', $data);
    }

    public function delete($id)
    {
        $this->load->model($this->model, 'cmodel');
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $this->cmodel->delete($id);
            $this->session->set_flashdata('message', array(
                'type' => 'success',
                'title' => 'O registro foi removido com sucesso!',
                'text' => validation_errors(),
            ));
            redirect($this->base_url, 303);
            die;
        }
        $title = $this->title . ': delete #' . $id;
        $url = $this->base_url . '/';
        $data = compact('title', 'url');
        $this->_render('delete', $data);
    }
}

