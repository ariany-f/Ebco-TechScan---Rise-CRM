<?php

namespace App\Controllers;

class Estimate_templates extends Security_Controller {

    protected $Estimate_templates_model;

    function __construct() {
        parent::__construct();
        $this->Estimate_templates_model = model("App\Models\Estimate_templates_model");
    }

    //load the estimate_template view
    function index() {
        $this->access_only_admin_or_settings_admin();
        return $this->template->view("estimate_templates/index");
    }

    //load the estimate_template add/edit modal
    function modal_form() {
        $this->access_only_admin_or_settings_admin();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Estimate_templates_model->get_one($this->request->getPost('id'));
        $view_data['estimate_templates_dropdown'] = array("" => "-") + $this->Estimate_templates_model->get_dropdown_list(array("title"), "id");
        return $this->template->view('estimate_templates/modal_form', $view_data);
    }

    function save_template() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $data = array(
            "template" => decode_ajax_post_data($this->request->getPost('template'))
        );
        $save_id = $this->Estimate_templates_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* load template edit form */

    function form($id = "") {
        validate_numeric_value($id);
        $this->access_only_admin_or_settings_admin();
        $view_data['model_info'] = $this->Estimate_templates_model->get_one($id);
        return $this->template->view('estimate_templates/form', $view_data);
    }

    //save a estimate_template
    function save() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));

        $id = $this->request->getPost('id');
        $copy_template = $this->request->getPost('copy_template');
        $data = array(
            "title" => $this->request->getPost('title'),
        );

        if ($copy_template) {
            $estimate_template = $this->Estimate_templates_model->get_one($copy_template);
            $data["template"] = $estimate_template->template;
        }

        $save_id = $this->Estimate_templates_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete or undo a estimate_template
    function delete() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        if (get_setting("default_estimate_template") === $id) {
            app_redirect("forbidden");
        }

        if ($this->request->getPost('undo')) {
            if ($this->Estimate_templates_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Estimate_templates_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //get estimate_template list data
    function list_data($view_type = "") {
        if (!$view_type) {
            $this->access_only_admin_or_settings_admin();
        }

        $list_data = $this->Estimate_templates_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $view_type);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of estimate_template list
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Estimate_templates_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    //make a row of estimate_template list table
    private function _make_row($data, $view_type = "") {
        if ($view_type === "modal") {
            return array("<a href='#' data-id='$data->id' class='estimate_template-row link'>" . $data->title . "</a>");
        } else {
            $delete = "";

            if (get_setting("default_estimate_template") !== $data->id) {
                $delete = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_estimate_template'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimate_templates/delete"), "data-action" => "delete"));
            }

            return array("<a href='#' data-id='$data->id' class='estimate_template-row link'>" . $data->title . "</a>",
                "<a class='edit'><i data-feather='code' class='icon-16'></i></a>" . modal_anchor(get_uri("estimate_templates/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "", "title" => app_lang('edit_estimate_template'), "data-post-id" => $data->id))
                . $delete
            );
        }
    }

    //show a modal to choose a template for estimate
    function insert_template_modal_form() {
        $this->init_permission_checker("estimate");
        $this->access_only_allowed_members();
        return $this->template->view("estimate_templates/insert_template_modal_form");
    }

    function get_template_data($id = 0) {
        if (!$id) {
            show_404();
        }

        $this->init_permission_checker("estimate");
        $this->access_only_allowed_members();

        $template_info = $this->Estimate_templates_model->get_one($id);
        echo json_encode(array("success" => true, 'template' => $template_info->template));
    }

}

/* End of file estimate_templates.php */
/* Location: ./app/controllers/estimate_templates.php */