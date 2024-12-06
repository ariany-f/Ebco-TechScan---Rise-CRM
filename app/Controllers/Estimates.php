<?php

namespace App\Controllers;

use App\Libraries\Mpdf;
use App\Libraries\Pdf;
use DOMDocument;
use DOMXPath;

class Estimates extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("estimate");
    }

    /* load estimate list view */

    function index() {
        $this->check_module_availability("module_estimate");
        $view_data['can_request_estimate'] = false;

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {
            $this->access_only_allowed_members();

            $view_data["conversion_rate"] = $this->get_conversion_rate_with_currency_symbol();
            return $this->template->rander("estimates/index", $view_data);
        } else {
            //client view
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            if (get_setting("module_estimate_request") == "1") {
                $view_data['can_request_estimate'] = true;
            }

            return $this->template->rander("clients/estimates/client_portal", $view_data);
        }
    }

    private function show_own_estimates_only_user_id() {
        if ($this->login_user->user_type === "staff") {
            return get_array_value($this->login_user->permissions, "estimate") == "own" ? $this->login_user->id : false;
        }
    }

    private function can_access_this_estimate($estimate_id = 0) {
        $estimate_info = $this->Estimates_model->get_one($estimate_id);

        if ($estimate_info->id && get_array_value($this->login_user->permissions, "estimate") == "own" && $estimate_info->created_by !== $this->login_user->id) {
            app_redirect("forbidden");
        }
    }

    private function can_access_this_estimate_item($estimate_item_id = 0) {
        $options = array("id" => $estimate_item_id);
        $item_info = $this->Estimate_items_model->get_details($options)->getRow();

        if ($item_info->id && get_array_value($this->login_user->permissions, "estimate") == "own" && $item_info->created_by !== $this->login_user->id) {
            app_redirect("forbidden");
        }
    }

    function uasg($uasg) {
        $url = "https://dadosabertos.compras.gov.br/modulo-uasg/1_consultarUasg?pagina=1&codigoUasg=$uasg&statusUasg=1";

        // Inicia a sessão cURL
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: */*'
        ));
        
        // Desabilitar a verificação do certificado SSL (apenas para testes)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        
        // Executa a requisição e captura a resposta
        $response = curl_exec($ch);
        
        // Verifica se ocorreu algum erro durante a requisição
        if(curl_errno($ch)) {
            $error = curl_error($ch);
            echo json_encode(['Erro cURL' => $error]);
        } else {
            // Tenta decodificar a resposta como JSON
            $decodedResponse = json_decode($response, true);
        
            // Verifica se a resposta é um JSON válido
            if (json_last_error() === JSON_ERROR_NONE) {
                // Resposta JSON válida, formatada
                echo json_encode($decodedResponse, JSON_PRETTY_PRINT);
            } else {
                // Resposta não é JSON válido, retorna a resposta bruta
                echo json_encode(['Erro' => 'Resposta não é um JSON válido', 'Resposta' => $response]);
            }
        }
        
        // Fecha a sessão cURL
        curl_close($ch);
    }

    //load the yearly view of estimate list
    function yearly() {
        return $this->template->view("estimates/yearly_estimates");
    }

    /* load new estimate modal */
    function modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);
        $client_id = $this->request->getPost('client_id');
        $model_info = $this->Estimates_model->get_one($id);
       
        $bidding_dropdown = [
            'serviços',
            'material',
        ];

        //check if proposal_id/contract_id/order_id posted. if found, generate related information
        $proposal_id = $this->request->getPost('proposal_id');
        $contract_id = $this->request->getPost('contract_id');
        $order_id = $this->request->getPost('order_id');
        $view_data['contract_id'] = $contract_id;
        $view_data['proposal_id'] = $proposal_id;
        $view_data['order_id'] = $order_id;
        if ($proposal_id || $contract_id || $order_id) {
            $info = null;
            if ($proposal_id) {
                $info = $this->Proposals_model->get_one($proposal_id);
            } else if ($contract_id) {
                $info = $this->Contracts_model->get_one($contract_id);
            } else if ($order_id) {
                $info = $this->Orders_model->get_one($order_id);
            }

            if ($info) {
                $now = get_my_local_time("Y-m-d");
                $model_info->estimate_date = $now;
                $model_info->estimate_type_id = $info->estimate_type_id;
                $model_info->valid_until = $now;
                $model_info->client_id = $info->client_id;
                $model_info->tax_id = $info->tax_id;
                $model_info->tax_id2 = $info->tax_id2;
                $model_info->discount_amount = $info->discount_amount;
                $model_info->discount_amount_type = $info->discount_amount_type;
                $model_info->discount_type = $info->discount_type;
            }
        }

        $view_data['model_info'] = $model_info;

        $estimate_request_id = $this->request->getPost('estimate_request_id');
        $view_data['estimate_request_id'] = $estimate_request_id;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['estimate_type_dropdown'] = array("" => "-") + $this->Estimate_type_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_clients_and_leads_dropdown();
       
        $view_data['client_id'] = $client_id;

        //clone estimate data
        $is_clone = $this->request->getPost('is_clone');
        $view_data['is_clone'] = $is_clone;

        $next_id = $this->Estimates_model->next_id()->getRow();
        $view_data['next_id'] = $next_id->id;

        $model_info->estimate_type_id = 1;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("estimates", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['companies_dropdown'] = $this->_get_companies_dropdown();
        $view_data['bidding_dropdown'] = $bidding_dropdown;
        if (!$model_info->company_id) {
            $view_data['model_info']->company_id = get_default_company_id();
        }
        
        return $this->template->view('estimates/modal_form', $view_data);
    }

    /* add, edit or clone an estimate */
    function save() {
      
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "estimate_client_id" => "required|numeric",
            "estimate_date" => "required",
            "valid_until" => "required",
            "estimate_request_id" => "numeric"
        ));

        $client_id = $this->request->getPost('estimate_client_id');
        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);

        if($this->request->getPost('estimate_number') && $this->Estimates_model->is_duplicate_code( $this->request->getPost('estimate_number'))) {
            echo json_encode(array("success" => false, 'message' => app_lang('estimate_code_exists')));
            return false;
        }

        $next_id = $this->Estimates_model->next_id()->getRow();
     
        $estimate_data = array(
            "client_id" => $client_id,
            "estimate_date" => $this->request->getPost('estimate_date'),
            "valid_until" => $this->request->getPost('valid_until'),
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "company_id" => $this->request->getPost('company_id') ? $this->request->getPost('company_id') : get_default_company_id(),
            "is_bidding" => $this->request->getPost('is_bidding'),
            "margem" => $this->request->getPost('margem'),
            "prazo_em_dias" => $this->request->getPost('prazo_em_dias'),
            "note" => $this->request->getPost('estimate_note')
        );
        
        $estimate_files = [];
        //is editing? update the files if required
        if ($id) {
            $files['file_names'] = $this->request->getPost("file_names");
            $files['file_sizes'] = $this->request->getPost("file_sizes");

            if($files["file_names"] && $files["file_sizes"])
            {
                foreach($files["file_names"] as $j => $file_name)
                {
                    $estimate_files[$j]["file_name"] = $file_name;
                }
                foreach($files["file_sizes"] as $j => $file_sizes)
                {
                    $estimate_files[$j]["file_size"] = $file_sizes;
                }
    
                foreach($estimate_files as $k => $file)
                {
                    $file_id = $this->save_file($estimate_files, $client_id, '', $file['file_size'], $file['file_name']);
                    
                    $estimate_files[$k]['file_id'] = (string)$file_id;
                }
            }
        }
        $estimate_data["files"] = serialize($estimate_files);

        $is_clone = $this->request->getPost('is_clone');
        $estimate_request_id = $this->request->getPost('estimate_request_id');
        $contract_id = $this->request->getPost('contract_id');
        $estimate_type_id = $this->request->getPost('estimate_type_id');
        $proposal_id = $this->request->getPost('proposal_id');
        $order_id = $this->request->getPost('order_id');

        // Add quando o código for ser gerado automaticamente
        if(!$id)
        {
            if($estimate_type_id !== 2 && $estimate_data['company_id'] != 3) {
                $estimate_data['estimate_number'] = $next_id->id;
            }
            else if( $estimate_data['company_id'] == 3)
            { 
                $next_zk_id = $this->Estimates_model->next_zk_id();
                $estimate_data['estimate_number_temp'] = "ZK_" . $next_zk_id;
            }
        }
        //estimate creation from estimate request
        //store the estimate request id for the first time only
        //don't copy estimate request id on cloning too
        if ($estimate_request_id && !$id && !$is_clone) {
            $estimate_data["estimate_request_id"] = $estimate_request_id;
        }

        $main_estimate_id = "";

        if (($is_clone && $id) || $order_id || $contract_id || $proposal_id) {
            $main_estimate_id = $id; //store main estimate id to get items later
            $id = ""; //on cloning estimate, save as new
            //save discount when cloning
            $estimate_data['estimate_type_id'] = $estimate_type_id;
            $estimate_data["discount_amount"] = $this->request->getPost('discount_amount') ? $this->request->getPost('discount_amount') : 0;
            $estimate_data["discount_amount_type"] = $this->request->getPost('discount_amount_type') ? $this->request->getPost('discount_amount_type') : "percentage";
            $estimate_data["discount_type"] = $this->request->getPost('discount_type') ? $this->request->getPost('discount_type') : "before_tax";
        }
    
        if (!$id) {
            $estimate_data["created_by"] = $this->login_user->id;
            $estimate_data["public_key"] = make_random_string();

            //add default template
            if (get_setting("default_estimate_template")) {
                $Estimate_templates_model = model("App\Models\Estimate_templates_model");
                $estimate_data["content"] = $Estimate_templates_model->get_one(get_setting("default_estimate_template"))->template;
            }
        }
        else{
            $options_estimate = array("id" => $id);
            $estimate_info_detail = $this->Estimates_model->get_details($options_estimate)->getRow();
            //log_notification("estimate_changed", array("estimate_id" => $id, "client_id" => $estimate_info_detail->company_id, "description" => $estimate_info_detail->public_key));
        }

        $estimate_id = $this->Estimates_model->ci_save($estimate_data, $id);
        if ($estimate_id) {
            // Altera lead para prospect em caso de ser lead
            $client = $this->Clients_model->get_one($client_id);
            if($client->lead_status_id == 1)
            {
                $data["lead_status_id"] = 2;
                $this->Clients_model->ci_save($data, $client_id);
            }

            if ($is_clone && $main_estimate_id) {
                //add estimate items
                save_custom_fields("estimates", $estimate_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate

                $estimate_items = $this->Estimate_items_model->get_all_where(array("estimate_id" => $main_estimate_id, "deleted" => 0))->getResult();
                
                foreach ($estimate_items as $estimate_item) {
                    //prepare new estimate item data
                    $estimate_item_data = (array) $estimate_item;
                    unset($estimate_item_data["id"]);
                    $estimate_item_data['estimate_id'] = $estimate_id;

                    $estimate_item = $this->Estimate_items_model->ci_save($estimate_item_data);
                }

            } else {
                save_custom_fields("estimates", $estimate_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_proposal/submitted copy_items_from_contract/copy_items_from_order? copy all items from the associated one
            $copy_items_from_proposal = $this->request->getPost("copy_items_from_proposal");
            $copy_items_from_contract = $this->request->getPost("copy_items_from_contract");
            $copy_items_from_order = $this->request->getPost("copy_items_from_order");
            $this->_copy_related_items_to_estimate($copy_items_from_proposal, $copy_items_from_contract, $copy_items_from_order, $estimate_id);

            echo json_encode(array("success" => true, 'id' => $estimate_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    // upload a file 
    function upload_estimate_file() {
        $this->access_only_allowed_members();
        upload_file_to_temp();
    }

    // check valid file for ticket 

    function validate_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    // download files 
    function download_files($id = 0) {
        $info = $this->Estimates_model->get_one($id);
        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }

    private function _copy_related_items_to_estimate($copy_items_from_proposal, $copy_items_from_contract, $copy_items_from_order, $estimate_id) {
        if (!($copy_items_from_proposal || $copy_items_from_contract || $copy_items_from_order)) {
            return false;
        }

        $copy_items = null;
        if ($copy_items_from_proposal) {
            $copy_items = $this->Proposal_items_model->get_details(array("proposal_id" => $copy_items_from_proposal))->getResult();
        } else if ($copy_items_from_contract) {
            $copy_items = $this->Contract_items_model->get_details(array("contract_id" => $copy_items_from_contract))->getResult();
        } else if ($copy_items_from_order) {
            $copy_items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->getResult();
        }

        if (!$copy_items) {
            return false;
        }

        foreach ($copy_items as $data) {
            $estimate_item_data = array(
                "estimate_id" => $estimate_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
            );

            $this->Estimate_items_model->ci_save($estimate_item_data);
        }
    }

    //update estimate status
    function update_estimate_status($estimate_id, $status, $is_modal = false) {
        if (!($estimate_id && $status)) {
            show_404();
        }

        validate_numeric_value($estimate_id);
        $this->can_access_this_estimate($estimate_id);
        $estmate_info = $this->Estimates_model->get_one($estimate_id);
        $this->access_only_allowed_members_or_client_contact($estmate_info->client_id);

        if ($this->login_user->user_type == "client") {
            //updating by client
            //client can only update the status once and the value should be either accepted or declined
            if (!($estmate_info->status == "sent" && ($status == "accepted" || $status == "declined" || $status == "in_revision"))) {
                show_404();
            }

            $estimate_data = array("status" => $status);

            //estimate acceptation with signature
            if ($is_modal) {
                if (!get_setting("add_signature_option_on_accepting_estimate") || $status !== "accepted") {
                    show_404();
                }

                $this->validate_submitted_data(array(
                    "signature" => "required"
                ));

                $meta_data = array();
                $signature = $this->request->getPost("signature");
                $signature = explode(",", $signature);
                $signature = get_array_value($signature, 1);
                $signature = base64_decode($signature);
                $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "estimate", NULL, "", $signature));

                $meta_data["signature"] = $signature;
                $meta_data["signed_date"] = get_current_utc_time();

                $estimate_data["meta_data"] = serialize($meta_data);
                $estimate_data["accepted_by"] = $this->login_user->id;
            }

            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //create notification
            if ($status == "accepted") {
                log_notification("estimate_accepted", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");

                //estimate accepted, create a new project
                if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted")) {
                    $this->_create_project_from_estimate($estimate_id);
                }

                $estimate_new_data = $this->Estimates_model->get_one($estimate_id);

                // Altera lead para client em caso de ser lead
                $client = $this->Clients_model->get_one($estimate_new_data->client_id);
                if($client->is_lead == 1)
                {
                    $data["is_lead"] = 0;
                    $this->Clients_model->ci_save($data, $estimate_new_data->client_id);
                }

                if ($is_modal) {
                    echo json_encode(array("success" => true, "message" => app_lang("estimate_accepted")));
                }
            } else if ($status == "declined") {
                log_notification("estimate_rejected", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
            }
        } else {
            //updating by team members
            if (!($status == "accepted" || $status == "declined" || $status == "in_revision" || $status == "sent")) {
                show_404();
            }

            $estimate_data = array("status" => $status);
            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //estimate accepted, create a new project
            if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted") && $status == "accepted") {
                $this->_create_project_from_estimate($estimate_id);
            }
            
            $estimate_new_data = $this->Estimates_model->get_one($estimate_id);
                
            // Altera lead para client em caso de ser lead
            $client = $this->Clients_model->get_one($estimate_new_data->client_id);
            if($client->is_lead == 1)
            {
                $data["is_lead"] = 0;
                $this->Clients_model->ci_save($data, $estimate_new_data->client_id);
            }
        }
    }

    /* create new project from accepted estimate */

    private function _create_project_from_estimate($estimate_id) {
        if ($estimate_id) {
            $this->can_access_this_estimate($estimate_id);
            $estimate_info = $this->Estimates_model->get_one($estimate_id);

            //don't create new project if there has already been created a new project with this estimate
            if (!$this->Projects_model->get_one_where(array("estimate_id" => $estimate_id))->id) {
                $data = array(
                    "title" => get_estimate_id($estimate_info->id),
                    "client_id" => $estimate_info->client_id,
                    "start_date" => $estimate_info->estimate_date,
                    "deadline" => $estimate_info->valid_until,
                    "estimate_id" => $estimate_id
                );
                $save_id = $this->Projects_model->ci_save($data);

                //save the project id
                $data = array("project_id" => $save_id);
                $this->Estimates_model->ci_save($data, $estimate_id);
            }
        }
    }

    /* create revision for estimate */
    function create_revision() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $this->can_access_this_estimate($id);
        $estimate_info = (array) $this->Estimates_model->get_one($id);
        unset($estimate_info['id']);

        $custom_fields_info = $this->Custom_fields_model->get_combined_details("estimates",  $id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();
      
        $cf_array = [];
        foreach ($custom_fields_info as $field) {
            $value = isset($field->value) ? $field->value : ""; // Pega o valor existente ou inicializa como vazio
            $cf_array[$field->id] = $value;
        }

      //  $next_id = $this->Estimates_model->next_id()->getRow();
        $estimate_info['parent_estimate'] = $estimate_info['estimate_number'] ?? $estimate_info['estimate_number_temp'];
        $estimate_info['estimate_number'] = null;
        $estimate_info['estimate_type_id'] = 2;
        $estimate_data["created_by"] = $this->login_user->id;
        $estimate_data["public_key"] = make_random_string();
        
        $estimate_id = $this->Estimates_model->ci_save($estimate_info, null);
       
        if ($estimate_id) {
            //add estimate items
            save_custom_fields("estimates", $estimate_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate

            $this->_save_custom_fields_of_estimate($estimate_id, $cf_array);

            $estimate_items = $this->Estimate_items_model->get_all_where(array("estimate_id" => $id, "deleted" => 0))->getResult();
            
            foreach ($estimate_items as $estimate_item) {
                //prepare new estimate item data
                $estimate_item_data = (array) $estimate_item;
                unset($estimate_item_data["id"]);
                $estimate_item_data['estimate_id'] = $estimate_id;

                $estimate_item = $this->Estimate_items_model->ci_save($estimate_item_data);
            }

            echo json_encode(array("success" => true, 'id' => $estimate_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an estimate */
    function delete() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);
        $estimate_info = $this->Estimates_model->get_one($id);

        if ($this->Estimates_model->delete($id)) {
            //delete signature file
            $signer_info = @unserialize($estimate_info->meta_data);
            if ($signer_info && is_array($signer_info) && get_array_value($signer_info, "signature")) {
                $signature_file = unserialize(get_array_value($signer_info, "signature"));
                delete_app_files(get_setting("timeline_file_path"), $signature_file);
            }
            $data = ['estimate_number' => null];
            $this->Estimates_model->ci_save($data, $id);

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of estimates, prepared for datatable  */
    function list_files_data($estimate_id = 0) {
        $this->access_only_allowed_members();      
        
        $list_data = $this->Estimates_model->get_files($estimate_id)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            require_once(APPPATH . "ThirdParty/nelexa-php-zip/vendor/autoload.php");
            $zip = new \PhpZip\ZipFile();
    
            if($data->files) {
                $files = unserialize($data->files);
                $total_files = count($files);
        
                //for only one file we'll download the file without archiving
                if ($total_files === 1) {
                    helper('download');
                }
                foreach ($files as $data) {
                    if($data["file_id"]){
                        $exists = $this->General_files_model->get_one_where(array("id" => $data["file_id"], "deleted" => 0));
                        
                        if ($exists->client_id) {
                            $result[] = $this->_make_files_row($data);
                        }
                    }
                }
            }
        }

        echo json_encode(array("data" => $result));
    }

    /* list of estimates, prepared for datatable  */
    function list_revisions_data($estimate_id = 0) {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
        $options = [
            "custom_fields" => $custom_fields
        ];
        $list_data = $this->Estimates_model->get_revisions( $options, $estimate_id )->getResult();
      
        $result = array();
        foreach ($list_data as $i => $data) {
            $result[] = $this->_make_revisions_row($data, $custom_fields, $i+1);
        }

        echo json_encode(array("data" => $result));
    }

    /* list of estimates, prepared for datatable  */
    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
        
        $seller_ids = $this->request->getPost('seller_ids') ? implode(",", $this->request->getPost('seller_ids')) : "";

        $options = array(
            "status" => $this->request->getPost("status"),
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "is_bidding" => $this->request->getPost("is_bidding"),
            "show_own_estimates_only_user_id" => $this->show_own_estimates_only_user_id(),
            "custom_fields" => $custom_fields,
            "seller_ids" => $seller_ids,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("estimates", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Estimates_model->get_details($options)->getResult();
      
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* list of estimate of a specific client, prepared for datatable  */

    function estimate_list_data_of_client($client_id) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "status" => $this->request->getPost("status"),
            "show_own_estimates_only_user_id" => $this->show_own_estimates_only_user_id(),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("estimates", $this->login_user->is_admin, $this->login_user->user_type)
        );

        if ($this->login_user->user_type == "client") {
            //don't show draft estimates to clients.
            $options["exclude_draft"] = true;
        }

        $list_data = $this->Estimates_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of estimate list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Estimates_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }


    function view_file($file_id = 0) {
        $file_info = $this->General_files_model->get_details(array("id" => $file_id))->getRow();

        if ($file_info) {

            if (!$file_info->client_id) {
                app_redirect("forbidden");
            }

            $this->can_access_this_client($file_info->client_id);

            $view_data['can_comment_on_files'] = false;
            $file_url = get_source_url_of_file(make_array_of_file($file_info), get_general_file_path("client", $file_info->client_id));

            $view_data["file_url"] = $file_url;
            $view_data["is_image_file"] = is_image_file($file_info->file_name);
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_info->file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_info->file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_info->file_name);
            $view_data["is_google_drive_file"] = ($file_info->file_id && $file_info->service_type == "google") ? true : false;
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_info->file_name);

            $view_data["file_info"] = $file_info;
            $view_data['file_id'] = clean_data($file_id);
            return $this->template->view("clients/files/view", $view_data);
        } else {
            show_404();
        }
    }
    
    function _make_files_row($data) {

        $file_icon = get_file_icon(strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION)));

        $description = "<div class='float-start'>" .
                js_anchor(remove_file_prefix($data['file_name']), array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("estimates/view_file/" . $data['file_id'])));

        if ($data['description']) {
            $description .= "<br /><span>" . $data['description'] . "</span></div>";
        } else {
            $description .= "</div>";
        }

        $options = anchor(get_uri("estimate/download_file/" . $data['file_id']), "<i data-feather='download-cloud' class='icon-16'></i>", array("title" => app_lang("download")));

        if ($this->login_user->user_type == "staff") {
            $options .= js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_file'), "class" => "delete", "data-id" => $data['file_id'], "data-action-url" => get_uri("estimates/delete_file"), "data-action" => "delete-confirmation"));
        }


        return array( $data['file_id'],
            "<div data-feather='$file_icon' class='mr10 float-start'></div>" . $description,
            convert_file_size($data['file_size']),
            $options
        );
    }

    /* prepare a row of estimate list table */

    private function _make_revisions_row($data, $custom_fields, $index) {
        $estimate_url = "";
        if ($this->login_user->user_type == "staff") {
            $estimate_url = anchor(get_uri("estimates/view/" . $data->id), get_estimate_id($data->id));
            $estimate_url_code = anchor(get_uri("estimates/view/" . $data->id), ($data->estimate_number ? $data->estimate_number : ($data->parent_estimate ? $data->parent_estimate : ($data->estimate_number_temp ? $data->estimate_number_temp : $data->id))));
        } else {
            //for client client
            $estimate_url = anchor(get_uri("estimates/preview/" . $data->id), get_estimate_id($data->id));
            $estimate_url_code = anchor(get_uri("estimates/preview/" . $data->id), ($data->estimate_number ? $data->estimate_number : ($data->parent_estimate ? $data->parent_estimate : ($data->estimate_number_temp ? $data->estimate_number_temp : $data->id))));
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }

        $row_data = array(
            $index,
            $this->_get_estimate_status_label($data),
        );

        $comment_link = "";
        if (get_setting("enable_comments_on_estimates") && $data->status !== "draft") {
            $comment_link = modal_anchor(get_uri("estimates/comment_modal_form"), "<i data-feather='message-circle' class='icon-16'></i>", array("class" => "edit text-muted", "title" => app_lang("estimate") . " #" . $data->id . " " . app_lang("comments"), "data-post-estimate_id" => $data->id));
        }

        $row_data[] = $comment_link;

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            if($field->title === 'Valor Estimado')
            {
                //$row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => to_currency((float)$data->$cf_id, $data->currency_symbol)));
                $row_data[] = str_replace(".", ",",$data->$cf_id);
            } else if($field->title === 'Termômetro')
            {
                $class = "badge-primary";
                $style = "";
                switch($data->$cf_id) {
                    case 'Morna':
                        $style = "border-left: 5px solid #FFB822 !important;";
                    break;
                    case 'Fria':
                        $style = "border-left: 5px solid #22B9FF !important;";
                     break;
                    case 'Quente':
                        $style = "border-left: 5px solid #FD397A !important;";
                    break;
                }
                $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => "<span style='padding:10px;$style'>" . $data->$cf_id . "</span>"));
            } else if($field->title === "Vendedor")
            {
                $collaborators_array = explode(',', $data->$cf_id);
                $user_result = '';
                foreach( $collaborators_array as $user )
                {
                    $user_info = $this->Users_model->get_one($user);
                    $user_result .= "<div class='user-avatar avatar-30 avatar-circle' data-bs-toggle='tooltip' title='" . $user_info->first_name .' '.$user_info->last_name . "'><img alt='' src='" . get_avatar($user_info->image, ($user_info->first_name .' '.$user_info->last_name)) . "'></div>";
                }
                $row_data[] = "<div class='w100 avatar-group'>" .  $user_result . "</div>";
            }
            else
            {
                $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
            }
        }


        $row_data[] = anchor(get_uri("estimate/preview/" . $data->id . "/" . $data->public_key), "<i data-feather='external-link' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('estimate') . " " . app_lang("url"), "target" => "_blank"))
                . modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_estimate'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_estimate'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    /* prepare a row of estimate list table */

    private function _make_row($data, $custom_fields) {
        $estimate_url = "";
        if ($this->login_user->user_type == "staff") {
            $estimate_url = anchor(get_uri("estimates/view/" . $data->id), get_estimate_id($data->id));
            $estimate_url_code = anchor(get_uri("estimates/view/" . $data->id), ($data->estimate_number ? $data->estimate_number : ($data->parent_estimate ? $data->parent_estimate : ($data->estimate_number_temp ? $data->estimate_number_temp : $data->id))));
        } else {
            //for client client
            $estimate_url = anchor(get_uri("estimates/preview/" . $data->id), get_estimate_id($data->id));
            $estimate_url_code = anchor(get_uri("estimates/preview/" . $data->id), ($data->estimate_number ? $data->estimate_number : ($data->parent_estimate ? $data->parent_estimate : ($data->estimate_number_temp ? $data->estimate_number_temp : $data->id))));
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }

        $licitacao = $data->is_bidding ? "Sim" : "Não";

        $row_data = array(
            $estimate_url_code,
            $estimate_url,
            $client,
            $data->estimate_date,
            format_to_date($data->estimate_date, false),
            '<span class="mt0 badge '.($data->estimate_type == "Revisão" ? "bg-info" : "bg-secondary").'  large">'. ($data->estimate_type == "Revisão" ? $data->estimate_type : "Primeira Proposta")  . '</span>',
            $this->_get_estimate_status_label($data),
            (($data->has_revisions) ? ("<span class='mt0 badge bg-success'>" . app_lang('yes') . "</span>") : ("<span class='mt0 badge bg-danger'>" . app_lang('no') . "</span>")),
            $licitacao,
        );

        $comment_link = "";
        if (get_setting("enable_comments_on_estimates") && $data->status !== "draft") {
            $comment_link = modal_anchor(get_uri("estimates/comment_modal_form"), "<i data-feather='message-circle' class='icon-16'></i>", array("class" => "edit text-muted", "title" => app_lang("estimate") . " #" . $data->id . " " . app_lang("comments"), "data-post-estimate_id" => $data->id));
        }

        $row_data[] = $comment_link;

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            if($field->title === 'Valor Estimado')
            {
              //  $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => to_currency((float)$data->$cf_id, $data->currency_symbol)));
              $row_data[] = str_replace(".", ",",$data->$cf_id);
            } else if($field->title === 'Termômetro')
            {
                $class = "badge-primary";
                $style = "";
                switch($data->$cf_id) {
                    case 'Morna':
                        $style = "border-left: 5px solid #FFB822 !important;";
                    break;
                    case 'Fria':
                        $style = "border-left: 5px solid #22B9FF !important;";
                     break;
                    case 'Quente':
                        $style = "border-left: 5px solid #FD397A !important;";
                    break;
                }
                $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => "<span style='padding:10px;$style'>" . $data->$cf_id . "</span>"));
            } else if($field->title === "Vendedor")
            {
                $collaborators_array = explode(',', $data->$cf_id);
                $user_result = '';
                foreach( $collaborators_array as $user )
                {
                    $user_info = $this->Users_model->get_one($user);
                    $user_result .= "<div class='user-avatar avatar-30 avatar-circle' data-bs-toggle='tooltip' title='" . $user_info->first_name .' '.$user_info->last_name . "'><img alt='' src='" . get_avatar($user_info->image, ($user_info->first_name .' '.$user_info->last_name)) . "'></div>";
                }
                $row_data[] = "<div class='w100 avatar-group'>" .  $user_result . "</div>";
            }
            else
            {
                $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
            }
        }


        $row_data[] = anchor(get_uri("estimate/preview/" . $data->id . "/" . $data->public_key), "<i data-feather='external-link' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('estimate') . " " . app_lang("url"), "target" => "_blank"))
                . modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_estimate'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_estimate'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete"), "data-action" => "delete-confirmation"))
                . ajax_anchor(get_uri("estimates/create_revision"), "<i data-feather='copy' class='icon-16'></i> ", array('title' => app_lang('create_revision'), "class" => "delete", "data-post-id" => $data->id, "data-reload-on-success" => true, "data-bs-toggle" => "tooltip", "data-placement" => "left"));

        return $row_data;
    }

    //prepare estimate status label 
    private function _get_estimate_status_label($estimate_info, $return_html = true) {
        return get_estimate_status_label($estimate_info, $return_html);
    }

    /* load estimate details view */

    function view($estimate_id = 0) {
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        if ($estimate_id) {
            validate_numeric_value($estimate_id);

            $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");
            $view_data = get_estimate_making_data($estimate_id);

            $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

            $comments_options = array(
                "estimate_id" => $estimate_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            if ($view_data) {
                $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
                $view_data['estimate_status'] = $this->_get_estimate_status_label($view_data["estimate_info"], false);

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $view_data["can_create_projects"] = $this->can_create_projects();

                $view_data["estimate_id"] = clean_data($estimate_id);

                return $this->template->rander("estimates/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    /* estimate total section */

    private function _get_estimate_total_view($estimate_id = 0) {
        $view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id);
        $view_data["estimate_id"] = $estimate_id;
        return $this->template->view('estimates/estimate_total_section', $view_data, true);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $view_data['model_info'] = $this->Estimates_model->get_one($estimate_id);

        return $this->template->view('estimates/discount_modal_form', $view_data);
    }

    function save_file($files, $client_id, $description, $file_size, $file_name) {

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $success = false;
        $now = get_current_utc_time();

        $target_path = getcwd() . "/" . get_general_file_path("client", $client_id);

        //process the fiiles which has been uploaded by dropzone
        if ($files && get_array_value($files, 0)) {
            foreach ($files as $file) {
                $file_name = $file_name;
                $file_info = move_temp_file($file_name, $target_path);
                if ($file_info) {
                    $data = array(
                        "client_id" => $client_id,
                        "file_name" => get_array_value($file_info, 'file_name'),
                        "file_id" => get_array_value($file_info, 'file_id'),
                        "service_type" => get_array_value($file_info, 'service_type'),
                        "description" => $description,
                        "file_size" => $file_size."_".$file,
                        "created_at" => $now,
                        "uploaded_by" => $this->login_user->id
                    );
                    return $this->General_files_model->ci_save($data);
                } else {
                   return false;
                }
            }
        }
    }

    /* save discount */

    function save_discount() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "estimate_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $data = array(
            "discount_type" => $this->request->getPost('discount_type'),
            "discount_amount" => $this->request->getPost('discount_amount'),
            "discount_amount_type" => $this->request->getPost('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Estimates_model->ci_save($data, $estimate_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "estimate_total_view" => $this->_get_estimate_total_view($estimate_id), 'message' => app_lang('record_saved'), "estimate_id" => $estimate_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* load item modal */

    function item_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $view_data['model_info'] = $this->Estimate_items_model->get_one($this->request->getPost('id'));
        if (!$estimate_id) {
            $estimate_id = $view_data['model_info']->estimate_id;
        }
        $view_data['estimate_id'] = $estimate_id;
        return $this->template->view('estimates/item_modal_form', $view_data);
    }

    /* add or edit an estimate item */
    function save_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $id = $this->request->getPost('id');
        $rate = unformat_currency($this->request->getPost('estimate_item_rate'));
        $quantity = unformat_currency($this->request->getPost('estimate_item_quantity'));
        $estimate_item_title = $this->request->getPost('estimate_item_title');
        $item_id = 0;

        if (!$id) {
            //on adding item for the first time, get the id to store
            $item_id = $this->request->getPost('item_id');
        }

        //check if the add_new_item flag is on, if so, add the item to libary. 
        $add_new_item_to_library = $this->request->getPost('add_new_item_to_library');
        if ($add_new_item_to_library) {
            $library_item_data = array(
                "title" => $estimate_item_title,
                "category_id" => 22,
                "description" => $this->request->getPost('estimate_item_description'),
                "unit_type" => $this->request->getPost('estimate_unit_type'),
                "rate" => unformat_currency($this->request->getPost('estimate_item_rate'))
            );
            $item_id = $this->Items_model->ci_save($library_item_data);
        }

        $estimate_item_data = array(
            "estimate_id" => $estimate_id,
            "title" => $this->request->getPost('estimate_item_title'),
            "description" => $this->request->getPost('estimate_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->request->getPost('estimate_unit_type'),
            "rate" => unformat_currency($this->request->getPost('estimate_item_rate')),
            "total" => $rate * $quantity,
        );

        if ($item_id) {
            $estimate_item_data["item_id"] = $item_id;
        }

        $estimate_item_id = $this->Estimate_items_model->ci_save($estimate_item_data, $id);

        if ($estimate_item_id) {
            $options_estimate = array("id" => $estimate_id);
            $estimate_info_detail = $this->Estimates_model->get_details($options_estimate)->getRow();
            $options = array("id" => $estimate_item_id);
            $item_info = $this->Estimate_items_model->get_details($options)->getRow();
          //  log_notification("estimate_changed", array("estimate_id" => $estimate_id, "client_id" => $estimate_info_detail->company_id, "description" => $estimate_info_detail->public_key));
            echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'id' => $estimate_item_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an estimate item */

    function delete_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate_item($id);
        if ($this->request->getPost('undo')) {
            if ($this->Estimate_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Estimate_items_model->get_details($options)->getRow();
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Estimate_items_model->delete($id)) {
                $item_info = $this->Estimate_items_model->get_one($id);
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of estimate items, prepared for datatable  */
    function item_list_data($estimate_id = 0) {
        validate_numeric_value($estimate_id);
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        $list_data = $this->Estimate_items_model->get_details(array("estimate_id" => $estimate_id))->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of estimate item list table */
    private function _make_item_row($data) {
        $item = "<div class='item-row strong mb5' data-id='$data->id'><div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div> $data->title - $data->category</div>";
        if ($data->description) {
            $item .= "<span class='text-wrap' style='margin-left:25px'>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("estimates/item_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_estimate'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of estimate item */

    function get_estimate_item_suggestion() {
        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->title . ' - ' . $item->category_title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . app_lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_estimate_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion(array("item_id" => $this->request->getPost("item_id")));
        if ($item) {
            $item->rate = $item->rate ? to_decimal_format($item->rate) : "";
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($estimate_id = 0, $show_close_preview = false,  $is_editor_preview = false, $show_acceptance = true) {

        $view_data = array();

        if ($estimate_id) {
            validate_numeric_value($estimate_id);

            $this->can_access_this_estimate($estimate_id);

            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);
            $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");

            $comments_options = array(
                "estimate_id" => $estimate_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            //get the label of the estimate
            $estimate_info = get_array_value($estimate_data, "estimate_info");
            
            $estimate_data['estimate_status_label'] = $this->_get_estimate_status_label($estimate_info);

           // $view_data['estimate_preview'] = prepare_estimate_pdf($estimate_data, "html");
            $view_data['estimate_preview'] = prepare_estimate_view($estimate_data);

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['estimate_id'] = $estimate_id;

            $view_data['show_acceptance'] = 0;
            
            if ($is_editor_preview) {
                $view_data["is_editor_preview"] = clean_data($is_editor_preview);
                return $this->template->view("estimates/estimate_preview", $view_data);
            } else {
                return $this->template->rander("estimates/estimate_preview", $view_data);
            }
        } else {
            show_404();
        }
    }
    
    function validate_code() {
        $code = $this->request->getPost('code');
        echo json_encode($this->Estimates_model->is_duplicate_code($code));
    }
    
    function next_zk_id() {
        echo json_encode($this->Estimates_model->next_zk_id());
    }

    function download_pdf($estimate_id = 0, $mode = "download", $estimate_public_id = 0) {
        if ($estimate_id) {
            validate_numeric_value($estimate_id);
            $this->can_access_this_estimate($estimate_id);
            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid estimate data. Prepare the view.

            prepare_estimate_pdf($estimate_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_estimate_access_permission($estimate_data) {
        //check for valid estimate
        if (!$estimate_data) {
            show_404();
        }

        //check for security
        $estimate_info = get_array_value($estimate_data, "estimate_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $estimate_info->client_id) {
                app_redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function get_estimate_status_bar($estimate_id = 0) {
        validate_numeric_value($estimate_id);
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        $view_data["estimate_info"] = $this->Estimates_model->get_details(array("id" => $estimate_id))->getRow();
        $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
        return $this->template->view('estimates/estimate_status_bar', $view_data);
    }

    function send_estimate_modal_form($estimate_id) {
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        if ($estimate_id) {
            validate_numeric_value($estimate_id);
            $options = array("id" => $estimate_id);
            $estimate_info = $this->Estimates_model->get_details($options)->getRow();

            $files = [];
            if($estimate_info->files)
            {
                $files = unserialize($estimate_info->files);
                foreach ($files as $f => $data) {
                    if($data["file_id"]){
                        $exists = $this->General_files_model->get_one_where(array("id" => $data["file_id"], "deleted" => 0));
                        
                        if (!$exists->client_id) {
                            unset($files[$f]);
                        }
                    }
                }
            }  
            
            $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
            $options = [
                "custom_fields" => $custom_fields
            ];
            $revisions = $this->Estimates_model->get_revisions($options, $estimate_id)->getResult();
            $revision_fl = [];
            $revision_files = [];
            log_message(1, count($revisions), []);
            foreach($revisions as $index => $revision)
            {
                if($revision->files)
                {
                    $revision_fl[] = unserialize($revision->files);
                    foreach ($revision_fl as $f => $data) {
                        if(count($data) > 0) {
                            $data = current($data);
                        }
                        if($data["file_id"]){
                            $exists = $this->General_files_model->get_one_where(array("id" => $data["file_id"], "deleted" => 0));
                            
                            $in_array = array_search($exists->id, array_column($revision_files, 'file_id')) !== false;

                            // Se não existir, adiciona o item ao array
                            if (!$in_array) {
                                $revision_files[] = [
                                    'file_index' => $index+1,
                                    'file_id' => $exists->id,
                                    'file_name' => $exists->file_name,
                                    'file_size' => $exists->file_size,
                                    'created_at' => $exists->created_at
                                ];
                            }
                        }
                    }
                }
            }

            $estimate_info->files = serialize($files);
            $estimate_info->revision_files = serialize($revision_files);

            $view_data['estimate_info'] = $estimate_info;

            $is_lead = $this->request->getPost('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $estimate_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $estimate_info->client_id);
            }

            $contacts = $this->Users_model->get_details($contacts_options)->getResult();
            $contact_first_name = "";
            $contact_last_name = "";
            $contacts_dropdown = array();
            foreach ($contacts as $contact) {
                if ($contact->is_primary_contact) {
                    $contact_first_name = $contact->first_name;
                    $contact_last_name = $contact->last_name;
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name . " (" . app_lang("primary_contact") . ")";
                }
            }

            foreach ($contacts as $contact) {
                if (!$contact->is_primary_contact) {
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name;
                }
            }

            $view_data['contacts_dropdown'] = $contacts_dropdown;

            $email_template = $this->Email_templates_model->get_final_template("estimate_sent");

            $parser_data["ESTIMATE_ID"] = $estimate_info->id;
            $parser_data["ESTIMATE_NUMBER"] = $estimate_info->estimate_number;
            $parser_data["COMPANY_IMAGE_URL_BIG"] = get_company_logo($estimate_info->company_id, "estimate_email", '100%');
            $parser_data["PUBLIC_ESTIMATE_URL"] = get_uri("estimate/preview/" . $estimate_info->id . "/" . $estimate_info->public_key);
            $parser_data["PUBLIC_ACCEPT_ESTIMATE_URL"] = get_uri("estimate/accept/" . $estimate_info->id . "/" . $estimate_info->public_key);
            $parser_data["PUBLIC_DECLINE_ESTIMATE_URL"] = get_uri("estimate/reject/" . $estimate_info->id . "/" . $estimate_info->public_key);
            $parser_data["PUBLIC_ESTIMATE_DOWNLOAD"] = get_uri("estimate/download_pdf/" . $estimate_info->id . "/" . $estimate_info->public_key);
            $parser_data["CONTACT_FIRST_NAME"] = $contact_first_name;
            $parser_data["CONTACT_LAST_NAME"] = $contact_last_name;
            $parser_data["PROJECT_TITLE"] = $estimate_info->project_title;
            $parser_data["ESTIMATE_URL"] = get_uri("estimates/preview/" . $estimate_info->id);
            $parser_data['SIGNATURE'] = $email_template->signature;
            $parser_data["LOGO_URL"] = get_logo_url();

            $message = $this->parser->setData($parser_data)->renderString($email_template->message);
            $subject = $this->parser->setData($parser_data)->renderString($email_template->subject);
            $view_data['message'] = htmlspecialchars_decode($message);
            $view_data['subject'] = htmlspecialchars_decode($subject);

            return $this->template->view('estimates/send_estimate_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function send_estimate() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('id');
        $this->can_access_this_estimate($estimate_id);

        $contact_id = $this->request->getPost('contact_id');
        $cc = $this->request->getPost('estimate_cc');

        $custom_bcc = $this->request->getPost('estimate_bcc');
        $subject = $this->request->getPost('subject');
        $message = decode_ajax_post_data($this->request->getPost('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $target_path = get_setting("timeline_file_path") . 'estimates/' . $estimate_id . '/';
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "estimate");
        $attachements = [];
        $files_data = unserialize($files_data);
        foreach($files_data as $i => $file)
        {
            $attachements[$i] = [
                'file_path' => getcwd().'/'.$target_path.$file['file_name']
            ];
        }

        $attachable_files = $this->request->getPost('attachable_files');
        if($attachable_files)
        {
            foreach($attachable_files as $i => $file)
            {
                $file = unserialize($file);
                    
                $file_info = $this->General_files_model->get_one($file['file_id']);
                $attachements[] = [
                    'file_path' => get_source_url_of_file(make_array_of_file($file_info), get_general_file_path("client", $file_info->client_id))
                ];
    
            }
        }
        else
        {
            echo json_encode(array('success' => false, 'message' => "Você precisa selecionar ao menos um arquivo para ir anexado"));
            return false;
        }

        $estimate_data = get_estimate_making_data($estimate_id);

        $default_bcc = get_setting('send_estimate_bcc_to');
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => $attachements, "cc" => $cc, "bcc" => $bcc_emails))) {
        //if (send_app_mail($contact->email, $subject, $message, array("attachments" => array([]), "cc" => $cc, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "sent", "last_email_sent_date" => get_my_local_time());
            if ($this->Estimates_model->ci_save($status_data, $estimate_id)) {
                echo json_encode(array('success' => true, 'message' => app_lang("estimate_sent_message"), "estimate_id" => $estimate_id));
            }
            // delete the temp estimate
            // if (file_exists($attachement_url)) {
            //     unlink($attachement_url);
            // }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update the sort value for estimate item
    function update_item_sort_values($id = 0) {

        $sort_values = $this->request->getPost("sort_values");
        if ($sort_values) {

            //extract the values from the comma separated string
            $sort_array = explode(",", $sort_values);

            //update the value in db
            foreach ($sort_array as $value) {
                $sort_item = explode("-", $value); //extract id and sort value

                $id = get_array_value($sort_item, 0);
                $sort = get_array_value($sort_item, 1);

                $data = array("sort" => $sort);
                $this->Estimate_items_model->ci_save($data, $id);
            }
        }
    }

    /* upload a post file */
    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for project */

    function validate_estimate_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    /* save estimate comments */

    function save_comment() {
        $estimate_id = $this->request->getPost('estimate_id');
        $now = get_current_utc_time();

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "estimate");

        $this->validate_submitted_data(array(
            "description" => "required",
            "estimate_id" => "required|numeric"
        ));

        $comment_data = array(
            "description" => $this->request->getPost('description'),
            "estimate_id" => $estimate_id,
            "created_by" => $this->login_user->id,
            "created_at" => $now,
            "files" => $files_data
        );

        $comment_data = clean_data($comment_data);
        $comment_data["files"] = $files_data; //don't clean serilized data

        $comment_id = $this->Estimate_comments_model->ci_save($comment_data);
        if ($comment_id) {
            $comments_options = array("id" => $comment_id);
            $view_data['comment'] = $this->Estimate_comments_model->get_details($comments_options)->getRow();
            $comment_view = $this->template->view("estimates/comment_row", $view_data, true);

            echo json_encode(array("success" => true, "data" => $comment_view, 'message' => app_lang('comment_submited')));
            log_notification("estimate_commented", array("estimate_id" => $estimate_id, "estimate_comment_id" => $comment_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete estimate comments */

    function delete_comment($id = 0) {

        if (!$id) {
            exit();
        }

        $comment_info = $this->Estimate_comments_model->get_one($id);

        //only admin and creator can delete the comment
        if (!($this->login_user->is_admin || $comment_info->created_by == $this->login_user->id)) {
            redirect("forbidden");
        }


        //delete the comment and files
        if ($this->Estimate_comments_model->delete($id) && $comment_info->files) {

            //delete the files
            $file_path = get_setting("timeline_file_path");
            $files = unserialize($comment_info->files);

            foreach ($files as $file) {
                $source_path = $file_path . get_array_value($file, "file_name");
                delete_file_from_directory($source_path);
            }
        }
    }

    /* delete a file */

    function delete_file() {

        $id = $this->request->getPost('id');
        $info = $this->General_files_model->get_one($id);

        if (!$info->client_id || ($this->login_user->user_type == "client" && $info->uploaded_by !== $this->login_user->id)) {
            app_redirect("forbidden");
        }

        $this->can_access_this_client($info->client_id);

        if ($this->General_files_model->delete($id)) {

            //delete the files
            delete_app_files(get_general_file_path("client", $info->client_id), array(make_array_of_file($info)));

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    function download_file($id) {
      
        $file_info = $this->General_files_model->get_one($id);

        if (!$file_info->client_id) {
            app_redirect("forbidden");
        }

        //serilize the path
        $file_data = serialize(array(make_array_of_file($file_info)));

        return $this->download_app_files(get_general_file_path("client", $file_info->client_id), $file_data);
    }

    /* download files by zip */

    function download_comment_files($id) {

        $files = $this->Estimate_comments_model->get_one($id)->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }

    function comment_modal_form() {
        $this->validate_submitted_data(array(
            "estimate_id" => "numeric|required"
        ));

        if (get_setting("enable_comments_on_estimates") !== "1") {
            app_redirect("forbidden");
        }

        $estimate_id = $this->request->getPost('estimate_id');
        $view_data['estimate_id'] = $estimate_id;

        $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");

        $view_data = get_estimate_making_data($estimate_id);

        $comments_options = array(
            "estimate_id" => $estimate_id,
            "sort_as_decending" => $sort_as_decending
        );
        $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
        $view_data["sort_as_decending"] = $sort_as_decending;

        return $this->template->view('estimates/comment_form', $view_data);
    }

    
    /**
     * Importação Excel
     */
    function import_estimates_modal_form() {
        $this->access_only_allowed_members();
        return $this->template->view("estimates/import_estimates_modal_form");
    }

    private function _prepare_estimate_data($data_row, $allowed_headers) {
        //prepare estimate data
        $Estimate_templates_model = model("App\Models\Estimate_templates_model");
        $estimate_data = array("status" => "draft", "public_key" => make_random_string(), "content" => $Estimate_templates_model->get_one(get_setting("default_estimate_template"))->template);
        $estimate_item_data = array();
        $custom_field_values_array = array();

        $Company_model = model('App\Models\Company_model');

        foreach ($data_row as $row_data_key => $row_data_value) {
            if (!$row_data_value) {
                continue;
            }

            $header_key_value = get_array_value($allowed_headers, $row_data_key);
            if (strpos($header_key_value, 'cf') !== false) { //custom field
                $explode_header_key_value = explode("-", $header_key_value);
                $custom_field_id = get_array_value($explode_header_key_value, 1);

                //modify date value
                $custom_field_info = $this->Custom_fields_model->get_one($custom_field_id);
                if ($custom_field_info->field_type === "date") {
                    $row_data_value = $this->_check_valid_date($row_data_value);
                }
                if($custom_field_info->field_type === "number")
                {
                    $formatted = str_replace(",", "", $row_data_value);
                    $formatted = str_replace(".", ",", $formatted);
                    $row_data_value = unformat_currency($formatted);
                }

                $custom_field_values_array[$custom_field_id] = $row_data_value;
            } else if ($header_key_value == "number") {
                $estimate_data["estimate_number"] = $row_data_value;
            } else if ($header_key_value == "created_at") {
                $estimate_data["estimate_date"] = $this->_check_valid_date($row_data_value);
            }  else if ($header_key_value == "is_revision") {
                $estimate_data["estimate_type_id"] = $row_data_value == "Sim" ? 2 : 1;
            } else if ($header_key_value == "is_bidding") {
                $estimate_data["is_bidding"] = $row_data_value == "Sim" ? 1 : 0;
            } else if ($header_key_value == "total_amount") {
              
            } else if ($header_key_value == "description") {
              
            } else if ($header_key_value == "valid_until") {
                $estimate_data["valid_until"] = $this->_check_valid_date($row_data_value);
            } else if ($header_key_value == "item") {
                $parts = explode('-', $row_data_value);
                if (count($parts) == 2) {
                    $equipment_name = trim($parts[0]);
                    $equipment_category = trim($parts[1]);
                } else {
                    // Lida com o caso onde o formato é inesperado
                    $equipment_name = $row_data_value;
                    $category_id = '22';
                }

                //get existing category, if not create new one and add the id
                if($equipment_category and (!empty($equipment_category)))
                {
                    $existing_category = $this->Item_categories_model->get_one_where(array("title" => $equipment_category, "deleted" => 0));
                    if ($existing_category->id) {
                        $category_id = $existing_category->id;
                    } else {
                        $category_data = array("title" => $equipment_category);
                        $category_id = $this->Item_categories_model->ci_save($category_data);
                    }
                }
                
                //get existing item, if not create new one and add the id
                $existing_item = $this->Items_model->get_one_where(array("title" => $equipment_name, "deleted" => 0, "category_id" => $category_id));
                if ($existing_item->id) {
                    $estimate_item_data["item_id"] = $existing_item->id;
                } else {
                    $item_data = array("title" => $equipment_name, "category_id" => $category_id);
                    $estimate_item_data["item_id"] = $this->Items_model->ci_save($item_data);
                }
                $estimate_item_data["title"] = $equipment_name;
            } else if ($header_key_value == "quantity") {
                $text_only = preg_replace('/[0-9]+/', '', $row_data_value);
                $estimate_item_data["unit_type"] = $text_only;
                $estimate_item_data["quantity"] = intval($row_data_value);
            }  else if ($header_key_value == "price") {
                $formatted = str_replace(",", "", $row_data_value);
                $formatted = str_replace(".", ",", $formatted);
                $estimate_item_data["rate"] = unformat_currency($formatted);
                if(isset($estimate_item_data["quantity"]) and (!empty($estimate_item_data["quantity"])))
                {
                    $estimate_item_data['total'] = unformat_currency($formatted) * $estimate_item_data["quantity"];
                }
            } else if ($header_key_value == "cnpj") {
                //get existing status, if not create new one and add the id
                $existing_client = $this->Clients_model->get_one_where(array("cnpj" => $row_data_value, "deleted" => 0));
                if ($existing_client->id) {
                    $estimate_data["client_id"] = $existing_client->id;
                } else {
                    $client_data = array("cnpj" => $row_data_value, "owner_id" => $this->login_user->id);
                    $estimate_data["client_id"] = $this->Clients_model->ci_save($client_data);
                }
            } else if ($header_key_value == "company_name") {
                //get existing company, if not create new one and add the id
                $existing_company = $Company_model->get_one_where(array("name" => $row_data_value, "deleted" => 0));
                if ($existing_company->id) {
                    $estimate_data["company_id"] = $existing_company->id;
                } else {
                    $company_data = array("name" => $row_data_value);
                    $estimate_data["company_id"] = $Company_model->ci_save($company_data);
                }
            }  else if ($header_key_value == "seller") {
                //get existing seller, if not create new one and add the id
                $existing_seller = $this->_get_user_id($row_data_value);
                if ($existing_seller) {
                    $estimate_data["created_by"] = $existing_seller;
                } else {
                    $user_data = array("first_name" => $row_data_value);
                    $estimate_data["created_by"] = $this->Users_model->ci_save($user_data);
                }
            } else {
                $estimate_data[$header_key_value] = $row_data_value;
            }
        }

        return array(
            "estimate_data" => $estimate_data,
            "estimate_item_data" => $estimate_item_data,
            "custom_field_values_array" => $custom_field_values_array
        );
    }

    private function _get_existing_custom_field_id($title = "") {
        if (!$title) {
            return false;
        }

        $custom_field_data = array(
            "title" => $title,
            "related_to" => "estimates"
        );

        $existing = $this->Custom_fields_model->get_one_where(array_merge($custom_field_data, array("deleted" => 0)));
        if ($existing->id) {
            return $existing->id;
        }
    }

    private function _prepare_headers_for_submit($headers_row, $headers) {
        foreach ($headers_row as $key => $header) {
            if (!((count($headers) - 1) < $key)) { //skip default headers
                continue;
            }

            //so, it's a custom field
            //check if there is any custom field existing with the title
            //add id like cf-3
            $existing_id = $this->_get_existing_custom_field_id($header);
            if ($existing_id) {
                array_push($headers, "cf-$existing_id");
            }
        }

        return $headers;
    }

    function save_estimate_from_excel_file() {
        if (!$this->validate_import_estimates_file_data(true)) {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }

        $file_name = $this->request->getPost('file_name');
        require_once(APPPATH . "ThirdParty/PHPOffice-PhpSpreadsheet/vendor/autoload.php");

        $temp_file_path = get_setting("temp_file_path");
        $excel_file = \PhpOffice\PhpSpreadsheet\IOFactory::load($temp_file_path . $file_name);
        $excel_file = $excel_file->getActiveSheet()->toArray();
        $allowed_headers = $this->_get_allowed_headers();
        $now = get_current_utc_time();

        foreach ($excel_file as $key => $value) { //rows
            if ($key === 0) { //first line is headers, modify this for custom fields and continue for the next loop
                $allowed_headers = $this->_prepare_headers_for_submit($value, $allowed_headers);
                continue;
            }

            $estimate_data_array = $this->_prepare_estimate_data($value, $allowed_headers);
            $estimate_data = get_array_value($estimate_data_array, "estimate_data");
            $estimate_item_data = get_array_value($estimate_data_array, "estimate_item_data");
            $custom_field_values_array = get_array_value($estimate_data_array, "custom_field_values_array");

            //couldn't prepare valid data
            if (!($estimate_data && count($estimate_data) > 1)) {
                continue;
            }

            //found information about lead, add some additional info
    //      $estimate_data["created_date"] = $now;
    //      $estimate_data["owner_id"] = $this->login_user->id;
    //      $estimate_item_data["created_at"] = $now;

            //save estimate data
            if($estimate_data['estimate_number'] and (!empty($estimate_data['estimate_number'])))
            {
                $existing_estimate = $this->Estimates_model->get_one_where(["estimate_number" => $estimate_data['estimate_number'], "deleted" => 0]);
                if( $existing_estimate->id )
                {
                    $estimate_save_id = $existing_estimate->id;
                }
                else
                {
                    $estimate_save_id = $this->Estimates_model->ci_save($estimate_data);
                    if (!$estimate_save_id) {
                        continue;
                    }
    
                    //save custom fields
                    $this->_save_custom_fields_of_estimate($estimate_save_id, $custom_field_values_array);
                }
    
                //add lead id to contact data
                $estimate_item_data["estimate_id"] = $estimate_save_id;
                $this->Estimate_items_model->ci_save($estimate_item_data);
            }
        }

        delete_file_from_directory($temp_file_path . $file_name); //delete temp file

        echo json_encode(array('success' => true, 'message' => app_lang("record_saved")));
    }

    private function _save_custom_fields_of_estimate($estimate_id, $custom_field_values_array) {
        if (!$custom_field_values_array) {
            return false;
        }

        foreach ($custom_field_values_array as $key => $custom_field_value) {
            $field_value_data = array(
                "related_to_type" => "estimates",
                "related_to_id" => $estimate_id,
                "custom_field_id" => $key,
                "value" => $custom_field_value
            );

            $field_value_data = clean_data($field_value_data);

            $this->Custom_field_values_model->ci_save($field_value_data);
        }
    }

     private function _get_allowed_headers() {
        return array(
            "number", // Código da Proposta
            "cnpj",
            "created_at",
            "is_revision",
            "total_amount",
            "is_bidding",
            "cf-1", //Thermometer
            "seller", // Ou cf-4
            "cf-5", // Estimated Value
            "item",
            "quantity",
            "price",
            "description",
            "cf-9", // Prazos
            "cf-8", // Condições de Pagamento
            "company_name",
            "valid_until"
        );
    }

    private function _store_headers_position($headers_row = array()) {
        $allowed_headers = $this->_get_allowed_headers();

        //check if all headers are correct and on the right position
        $final_headers = array();
        foreach ($headers_row as $key => $header) {
            if (!$header) {
                continue;
            }

            $key_value = str_replace(' ', '_', strtolower(trim($header, " ")));
            $header_on_this_position = get_array_value($allowed_headers, $key);
            $header_array = array("key_value" => $header_on_this_position, "value" => $header);

            if ($header_on_this_position == $key_value) {
                //allowed headers
                //the required headers should be on the correct positions
                //the rest headers will be treated as custom fields
                //pushed header at last of this loop
            } else if (((count($allowed_headers) - 1) < $key) && $key_value) {
                //custom fields headers
                //check if there is any existing custom field with this title
                $existing_id = $this->_get_existing_custom_field_id(trim($header, " "));
                if ($existing_id) {
                    $header_array["custom_field_id"] = $existing_id;
                } else {
                    $header_array["has_error"] = true;
                    $header_array["custom_field"] = true;
                }
            } else { //invalid header, flag as red
                $header_array["has_error"] = true;
            }

            if ($key_value) {
                array_push($final_headers, $header_array);
            }
        }

        return $final_headers;
    }

    function validate_import_estimates_file() {
        $file_name = $this->request->getPost("file_name");
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!is_valid_file_to_upload($file_name)) {
            echo json_encode(array("success" => false, 'message' => app_lang('invalid_file_type')));
            exit();
        }

        if ($file_ext == "xlsx") {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('please_upload_a_excel_file') . " (.xlsx)"));
        }
    }

    function validate_import_estimates_file_data($check_on_submit = false) {
        $table_data = "";
        $error_message = "";
        $headers = array();
        $got_error_header = false; //we've to check the valid headers first, and a single header at a time
        $got_error_table_data = false;

        $file_name = $this->request->getPost("file_name");

        require_once(APPPATH . "ThirdParty/PHPOffice-PhpSpreadsheet/vendor/autoload.php");

        $temp_file_path = get_setting("temp_file_path");
        $excel_file = \PhpOffice\PhpSpreadsheet\IOFactory::load($temp_file_path . $file_name);
        $excel_file = $excel_file->getActiveSheet()->toArray();

        $table_data .= '<table class="table table-responsive table-bordered table-hover" style="width: 100%; color: #444;">';

        $table_data_header_array = array();
        $table_data_body_array = array();

        foreach ($excel_file as $row_key => $value) {
            if ($row_key == 0) { //validate headers
                $headers = $this->_store_headers_position($value);

                foreach ($headers as $row_data) {
                    $has_error_class = false;
                    if (get_array_value($row_data, "has_error") && !$got_error_header) {
                        $has_error_class = true;
                        $got_error_header = true;

                        if (get_array_value($row_data, "custom_field")) {
                            $error_message = app_lang("no_such_custom_field_found");
                        } else {
                            $error_message = sprintf(app_lang("import_client_error_header"), app_lang(get_array_value($row_data, "key_value")));
                        }
                    }

                    array_push($table_data_header_array, array("has_error_class" => $has_error_class, "value" => get_array_value($row_data, "value")));
                }
            } else { //validate data
                if (!array_filter($value)) {
                    continue;
                }

                $error_message_on_this_row = "<ol class='pl15'>";

                foreach ($value as $key => $row_data) {
                    $has_error_class = false;

                    if (!$got_error_header) {
                        $row_data_validation = $this->_row_data_validation_and_get_error_message($key, $row_data, $headers);
                        if ($row_data_validation) {
                            $has_error_class = true;
                            $error_message_on_this_row .= "<li>" . $row_data_validation . "</li>";
                            $got_error_table_data = true;
                        }
                    }

                    if (count($headers) > $key) {
                        $table_data_body_array[$row_key][] = array("has_error_class" => $has_error_class, "value" => $row_data);
                    }
                }

                $error_message_on_this_row .= "</ol>";

                //error messages for this row
                if ($got_error_table_data) {
                    $table_data_body_array[$row_key][] = array("has_error_text" => true, "value" => $error_message_on_this_row);
                }
            }
        }

        //return false if any error found on submitting file
        if ($check_on_submit) {
            return ($got_error_header || $got_error_table_data) ? false : true;
        }

        //add error header if there is any error in table body
        if ($got_error_table_data) {
            array_push($table_data_header_array, array("has_error_text" => true, "value" => app_lang("error")));
        }

        //add headers to table
        $table_data .= "<tr>";
        foreach ($table_data_header_array as $table_data_header) {
            $error_class = get_array_value($table_data_header, "has_error_class") ? "error" : "";
            $error_text = get_array_value($table_data_header, "has_error_text") ? "text-danger" : "";
            $value = get_array_value($table_data_header, "value");
            $table_data .= "<th class='$error_class $error_text'>" . $value . "</th>";
        }
        $table_data .= "</tr>";

        //add body data to table
        foreach ($table_data_body_array as $table_data_body_row) {
            $table_data .= "<tr>";
            $error_text = "";

            foreach ($table_data_body_row as $table_data_body_row_data) {
                $error_class = get_array_value($table_data_body_row_data, "has_error_class") ? "error" : "";
                $error_text = get_array_value($table_data_body_row_data, "has_error_text") ? "text-danger" : "";
                $value = get_array_value($table_data_body_row_data, "value");
                $table_data .= "<td class='$error_class $error_text'>" . $value . "</td>";
            }

            if ($got_error_table_data && !$error_text) {
                $table_data .= "<td></td>";
            }

            $table_data .= "</tr>";
        }

        //add error message for header
        if ($error_message) {
            $total_columns = count($table_data_header_array);
            $table_data .= "<tr><td class='text-danger' colspan='$total_columns'><i data-feather='alert-triangle' class='icon-16'></i> " . $error_message . "</td></tr>";
        }

        $table_data .= "</table>";

        echo json_encode(array("success" => true, 'table_data' => $table_data, 'got_error' => ($got_error_header || $got_error_table_data) ? true : false));
    }

    private function _row_data_validation_and_get_error_message($key, $data, $headers = array()) {
        $allowed_headers = $this->_get_allowed_headers();
        $header_value = get_array_value($allowed_headers, $key);

        //company name field is required
        // if ($header_value == "cnpj" && !$data) {
        //     return app_lang("import_client_error_company_name_field_required");
        // }

         //check dates
         if ($header_value == "valid_until" && !$this->_check_valid_date($data)) {
            return app_lang("import_date_error_message");
        }
        if ($header_value == "estimate_date" && !$this->_check_valid_date($data)) {
           return app_lang("import_date_error_message");
        }

        //check existance
        if ($data && (
            ($header_value == "company_name" && !$this->_get_company_id($data)) ||
            ($header_value == "seller" && !$this->_get_user_id($data)))) {
            return sprintf(app_lang("import_not_exists_error_message"), app_lang($header_value));
        }


        //there has no date field on default import fields
        //check on custom fields
        if (((count($allowed_headers) - 1) < $key) && $data) {
            $header_info = get_array_value($headers, $key);
            $custom_field_info = $this->Custom_fields_model->get_one(get_array_value($header_info, "custom_field_id"));
            if ($custom_field_info->field_type === "date" && !$this->_check_valid_date($data)) {
                return app_lang("import_date_error_message");
            }
        }
    }

    function download_sample_excel_file() {
        $this->access_only_allowed_members();
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-estimates-sample.xlsx"))));
    }

    function upload_excel_file() {
        upload_file_to_temp(true);
    }    

    private function _get_company_id($company = "") {
        $company = trim($company);
        if (!$company) {
            return false;
        }

        $Company_model = model('App\Models\Company_model');

        $existing_company = $Company_model->get_company_from_name($company);
        if ($existing_company) {
            return $existing_company->id;
        } else {
            return false;
        }
    }

    

    private function _get_user_id($user = "") {
        $user = trim($user);
        if (!$user) {
            return false;
        }

        $existing_user = $this->Users_model->get_user_from_full_name($user, "staff");
        if ($existing_user) {
            return $existing_user->id;
        } else {
            return false;
        }
    }

    /**
     * End Importação Excel
     */

    function load_statistics_of_selected_currency($currency = "") {
        if ($currency) {
            $statistics = estimate_sent_statistics_widget(array("currency" => $currency));

            if ($statistics) {
                echo json_encode(array("success" => true, "statistics" => $statistics));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            }
        }
    }

    function get_sellers_estimates($type = 'monthly', $pos = false) {
        
        $seller_id = $this->request->getPost('seller');
        $options = array(
            "pos" => $pos,
            "seller_id" => $seller_id,
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
        );

        $all_options = append_server_side_filtering_commmon_params($options);

        $result = $this->Estimates_model->get_sellers_estimates($all_options);
        
        if (get_array_value($all_options, "server_side")) {
            $list_data = get_array_value($result, "data");
        } else {
            $list_data = $result->getResult();
            $result = array();
        }


        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_sellers_estimate_list_row($data, $type);
        }
        
        $result["data"] = $result_data;

        echo json_encode($result);
    }

    private function _make_sellers_estimate_list_row($data, $type) {
        $collaborators = "";
        $collaborator_parts = explode("--::--", $data->Vendedor);

        $collaborator_id = get_array_value($collaborator_parts, 0);
        $collaborator_name = get_array_value($collaborator_parts, 1);

        $image_url = get_avatar(get_array_value($collaborator_parts, 2), $collaborator_name);

        $collaboratr_image = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt='$collaborator_name'></span> $collaborator_name";
       
        $collaborators .= get_team_member_profile_link($collaborator_id, $collaboratr_image, array("title" => $collaborator_name)); 

        $conversao_class = "bg-primary";
        if($data->Conversao < 1)
        {
            $conversao_class = "bg-danger";
        }
        else if($data->Conversao < 30) {
            
            $conversao_class = "bg-warning";
        }

        $conversao = "<span class='badge mt0 $conversao_class' title='$data->Conversao'><b>$data->Conversao</b></span>";

        if($type == 'monthly')
        {
            $row_data = array(
                $collaborators,
                $data->Propostas_Emitidas,
                $data->Propostas_Fechadas,
                $conversao,
                $data->Valor_Fechado
            );
        }
        else{
            $row_data = array(
                translate_month_name($data->Mes),
                $collaborators,
                $data->Propostas_Emitidas,
                $data->Propostas_Fechadas,
                $conversao,
                $data->Valor_Fechado
            );
        }

        return $row_data;
    }

    function get_coligadas_estimates($type = 'monthly') {
        
        $coligada_id = $this->request->getPost('coligada');
        $options = array(
            "coligada" => $coligada_id,
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
        );

        $all_options = append_server_side_filtering_commmon_params($options);

        $result = $this->Estimates_model->get_coligadas_estimates($all_options);
        
        if (get_array_value($all_options, "server_side")) {
            $list_data = get_array_value($result, "data");
        } else {
            $list_data = $result->getResult();
            $result = array();
        }


        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_coligadas_estimate_list_row($data, $type);
        }
        
        $result["data"] = $result_data;

        echo json_encode($result);
    }

    private function _make_coligadas_estimate_list_row($data, $type = 'monthly') {
        $conversao_class = "bg-primary";
        if($data->Conversao < 1)
        {
            $conversao_class = "bg-danger";
        }
        else if($data->Conversao < 30) {
            
            $conversao_class = "bg-warning";
        }

        $conversao = "<span class='badge mt0 $conversao_class' title='$data->Conversao'><b>$data->Conversao</b></span>";

        if($type == 'monthly')
        {
            $row_data = array(
                $data->Nome_Empresa,
                $data->Propostas_Emitidas,
                $data->Propostas_Fechadas,
                $conversao,
                $data->Valor_Fechado
            );
        }
        else
        {
            $row_data = array(
                translate_month_name($data->Mes),
                $data->Nome_Empresa,
                $data->Propostas_Emitidas,
                $data->Propostas_Fechadas,
                $conversao,
                $data->Valor_Fechado
            );
        }

        return $row_data;
    }
    
    function save_view() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost("id");

        $estimate_data = array(
            "content" => decode_ajax_post_data($this->request->getPost('view'))
        );

        $this->Estimates_model->ci_save($estimate_data, $id);

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    function editor($estimate_id = 0) {
        validate_numeric_value($estimate_id);
        $view_data['estimate_info'] = $this->Estimates_model->get_details(array("id" => $estimate_id))->getRow();
        return $this->template->view("estimates/estimate_editor", $view_data);
    }
}



/**
 * 
 * get company logo
 * @param Int $company_id
 * @return string
 * AJUSTAR LOGO DA COLIGADA NA PROPOSTA E NA PROPOSIÇÃO
 */
if (!function_exists('get_company_logo')) {

    function get_company_logo($company_id, $type = "", $size = "300px") {
        $Company_model = model('App\Models\Company_model');
        $company_info = $Company_model->get_one($company_id);
        $only_file_path = get_setting('only_file_path');

        if($type == 'estimate')
        {
            if (isset($company_info->logo) && $company_info->logo) {
                $file = unserialize($company_info->logo);
                if (is_array($file)) {
                    $file = get_array_value($file, 0);

                    return '<img style="max-width: '.$size.';width: 100%;" class="max-logo-size" src="'. get_source_url_of_file($file, get_setting("system_file_path"), "thumbnail", $only_file_path, $only_file_path) .'" alt="..." />';

                }
            } else {
                $logo = $type . "_logo";
                if (!get_setting($logo)) {
                    $logo = "invoice_logo";
                }
    
                return '<img style="max-width: '.$size.';" class="max-logo-size" src="'. get_file_from_setting($logo, $only_file_path) .'" alt="..." />';
    

            }
        }
        
        if($type == 'estimate_email')
        {
            if (isset($company_info->logo) && $company_info->logo) {
                $file = unserialize($company_info->logo);
                if (is_array($file)) {
                    $file = get_array_value($file, 0);
                    return get_source_url_of_file($file, get_setting("system_file_path"), "thumbnail", $only_file_path, $only_file_path, 1);
                }
            }
            else {
                return '';
            }
        }

        if($type == 'proposal')
        {
            if (isset($company_info->logo) && $company_info->logo) {
                $file = unserialize($company_info->logo);
                if (is_array($file)) {
                    $file = get_array_value($file, 0);
                    ?>
                    <!-- <img class="pasted-image" src="<?php echo get_source_url_of_file($file, get_setting("system_file_path"), "thumbnail", $only_file_path, $only_file_path); ?>" alt="..." /> -->
                    <?php
                }
            } else {
                $logo = $type . "_logo";
                if (!get_setting($logo)) {
                    $logo = "invoice_logo";
                }
                ?>
    
                <!-- <img class="pasted-image" src="<?php echo get_file_from_setting($logo, $only_file_path); ?>" alt="..." /> -->
    
                <?php
            }
        }
        else
        {
            if (isset($company_info->logo) && $company_info->logo) {
                $file = unserialize($company_info->logo);
                if (is_array($file)) {
                    $file = get_array_value($file, 0);
                    ?>
                    <img class="max-logo-size" src="<?php echo get_source_url_of_file($file, get_setting("system_file_path"), "thumbnail", $only_file_path, $only_file_path); ?>" alt="..." />
                    <?php
                }
            } else {
                $logo = $type . "_logo";
                if (!get_setting($logo)) {
                    $logo = "invoice_logo";
                }
                ?>
    
                <img class="max-logo-size" src="<?php echo get_file_from_setting($logo, $only_file_path); ?>" alt="..." />
    
                <?php
            }
        }
    }

}

/* End of file estimates.php */
    /* Location: ./app/controllers/estimates.php */    