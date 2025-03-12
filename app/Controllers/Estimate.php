<?php

namespace App\Controllers;
use App\Libraries\Dompdf;
use App\Libraries\DompdfOptions;
use DateTime;

class Estimate extends Security_Controller {

    function __construct() {
        parent::__construct(false);
    }

    function index() {
        app_redirect("forbidden");
    }
    
    //print estimate
    function print_estimate($estimate_id = 0, $public_key = 0, $acceptance = 0) {
            
        if (!($estimate_id && $public_key)) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        
        if ($estimate_info->public_key !== $public_key) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }

        $view_data = get_estimate_making_data($estimate_id);
        if (!$view_data) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }

       // $view_data['estimate_preview'] = prepare_estimate_pdf($view_data, "html");

        $postdata = http_build_query(
            array(
                'buttons' => false
            )
        );
        
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        
        $context  = stream_context_create($opts);
        
        // Pegar da previa e exibir no pdf ao imprimir pdf
        $html = file_get_contents('http://'.$_SERVER['HTTP_HOST']."/crm/estimate/preview/" . $estimate_id . "/" . $public_key . '/' . $acceptance, false, $context);
       
        $view_data['estimate_preview'] = $html;

        echo json_encode(array("success" => true, "print_view" => $view_data['estimate_preview']));
    }

    function preview($estimate_id = 0, $public_key = "", $show_acceptance = 1) {

        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $view_data['estimate_preview'] = $show_acceptance == 0 ? prepare_estimate_view($estimate_data) : "";
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_info'] = $estimate_info;
        $view_data['estimate_id'] = $estimate_id;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);
        $view_data['show_acceptance'] = $show_acceptance;

        $view_data['buttons'] = $this->request->getPost("buttons");
        
        return view("estimates/estimate_public_preview", $view_data);
    }

    function reject($estimate_id = 0, $public_key = "", $show_acceptance = 1) {

        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $view_data['estimate_preview'] = $show_acceptance == 0 ? prepare_estimate_view($estimate_data) : "";
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_info'] = $estimate_info;
        $view_data['estimate_id'] = $estimate_id;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);
        if($estimate_info->status != 'rejected') {
            $view_data['show_info_fields'] = true;
        }

        $view_data['buttons'] = $this->request->getPost("buttons");
        
        return view("estimates/estimate_public_reject", $view_data);
    }

    function accept($estimate_id = 0, $public_key = "", $show_acceptance = 1) {

        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
        $options = array("id" => $estimate_id, "custom_fields" => $custom_fields);
        $estimate_info = $this->Estimates_model->get_details($options)->getRow();

        $estimate_info->custom_fields = $this->Custom_fields_model->get_combined_details("estimates", $estimate_id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();
      
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $view_data['estimate_preview'] = $show_acceptance == 0 ? prepare_estimate_view($estimate_data) : "";
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_info'] = $estimate_info;
        $view_data['estimate_id'] = $estimate_id;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);
        if($estimate_info->status != 'accepted') {
            $view_data['show_info_fields'] = true;
        }

        $view_data['buttons'] = $this->request->getPost("buttons");
        
        return view("estimates/estimate_public_accept", $view_data);
    }

    function download_file($id) {
        
        if (!($id)) {
            show_404();
        }

        $file_info = $this->General_files_model->get_one($id);

        if (!$file_info->client_id) {
            app_redirect("forbidden");
        }

        //serilize the path
        $file_data = serialize(array(make_array_of_file($file_info)));

        return $this->download_app_files(get_general_file_path("client", $file_info->client_id), $file_data);
    }
    
    function download_pdf($estimate_id = 0, $public_key = "", $js = 1) {
        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $view_data['estimate_preview'] = prepare_estimate_view($estimate_data, 0);
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_id'] = $estimate_id;
        $view_data['show_js'] = $js;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);

        return view("estimates/estimate_download_pdf", $view_data);
    }
    
    function download_pdf_direct($estimate_id = 0, $public_key = "", $js = 1) {
        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $postdata = http_build_query(
            array()
        );
        
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => 'Content-Type: application/json',
                'content' => $postdata
            )
        );
        
        $context  = stream_context_create($opts);

        $view_data['test'] = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST']."/crm/estimate/print_estimate/" . $estimate_info->id . "/" . $estimate_info->public_key . '/0', false, $context), true);
        $view_data['estimate_preview'] = prepare_estimate_view($estimate_data);
            
        // Inicializa o Dompdf
        $options = new DompdfOptions();
        //$options->set('isPhpEnabled', true); // Permite PHP dentro do HTML
        //$options->set('isHtml5ParserEnabled', true); // Permite PHP dentro do HTML
        $options->setIsRemoteEnabled(true);
        $options->set('chroot', '/'); // Desabilita o "chroot", permitindo acesso a imagens externas

        // Inicializa o Dompdf
        $dompdf = new Dompdf($options);

        // Conteúdo HTML a ser adicionado ao PDF
        $html_content_old = $view_data['estimate_preview'];

        // Substitui o conteúdo dentro das tags <img>
        $html_content = preg_replace_callback('/<img([^>]*)src=["\'](.*?)["\']([^>]*)>/i',  function($matches) {
            // Aqui você pode adicionar a lógica para substituir o conteúdo dentro do src=""
            // Neste exemplo, estamos simplesmente substituindo por um texto fixo

            $path = $matches[2];

            if (strpos($path, 'http://') !== 0 && strpos($path, 'https://') !== 0) {
                // Se não começar com "http://" ou "https://", acrescenta "https://"
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $path = $protocol . $_SERVER['HTTP_HOST'] . $path;
            }

            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return '<img class="inside-proposal" src="'.$base64.'" alt="' . $matches[3] . '">';
        }, $html_content_old);


        $path = getcwd() . '/assets/images/header.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Adiciona cabeçalho e rodapé ao HTML
        $html_with_header_footer = '
            <html>
            <head>
                <style>
                    /* Estilo para o cabeçalho */
                    .header-pdf {
                        position: fixed;
                        top: -220px;
                        left: 0;
                        right: 0;
                        height: 80px;
                        text-align: center;
                        line-height: 20px;
                    }
                    .inside-proposal {
                        max-width: 100%!important;
                    }
                    body {
                        margin-top: 0px;
                        margin-bottom: 0px;
                        max-width: 100%;
                    }
                    .content td, .content td p {
                        max-width: 90vw;
                    }
                    /* Estilo para o rodapé */
                    .footer-pdf {
                        width:100%;
                        position: fixed;
                        width: 100%;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        height: 20px;
                        text-align: center;
                        color: #1f497d;
                        font-family: "Helvetica", sans-serif;
                    }
                    /* Estilo para o conteúdo */
                    .content-pdf {
                        font-family: "Helvetica", sans-serif;
                        color: #1f497d;
                    }
                    table tr {
                        text-align: center;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        font-weight:700;
                    }
                    table tr:not(.header) td {
                        padding-top: .6rem;
                        padding-bottom: .6rem;
                    }
                    table .title td{
                        padding-top: 1.5rem!important;
                        padding-bottom: 1rem!important;
                    }
                    table .title {
                        font-size: 16px;
                        line-height: 18px;
                    }
                    table .subtitle {
                        font-size: 14px;
                        line-height: 16px;
                        text-align: left;
                    }
                    table .content {
                        text-align: justify;
                        font-size: 14px;
                        line-height: 16px;
                    }
                    table .content td{
                        padding-top: 0!important;
                        color: rgb(70, 70, 70);
                    }
                    table .assinatura td {
                        padding-top: 1rem!important;
                        clear: both;
                    }
                    table .assinatura img{
                        width: 50%;
                    }            
                    table .image-centro td {
                        padding-top: 1rem!important;
                        clear: both;
                    }
                    table .image-centro img{
                        width: 50%;
                    }
                    table img {
                        margin: 0 auto;
                    }
                    table .bordered-table td {
                        border: 1px outset var(--primary-color);
                        border-radius: 5px;
                        display: grid;
                        text-align: left;
                        width: 100%;
                        padding: 1rem;
                        margin-top: 1rem;
                        margin-bottom: 1rem;
                    }

                    table.no-gut {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                    }
                    table.items, table.items td{
                        min-width: 100%;
                        border: .8px solid #ededed;
                        height: 2rem;
                        padding: 0 !important;
                        vertical-align: middle;
                        line-height: 14px;
                        font-size: 13px;
                    }
                    table.items .table-header {
                        line-height: 20px!important;
                        height: 2.2rem;
                        background-color: #1d497d; 
                        color: #fff;
                    }
                    table.items .table-header td{
                        border: .8px solid #395577!important;
                    }
                    table.items .table-sum {
                        background-color: #cacfe1; 
                        line-height: 20px;
                        font-size: 14px;
                        margin-bottom: 20px;
                    }
                    table.items .table-content {
                        line-height: 20px;
                        font-size: 14px;
                    }
                    .deslocamentos {
                        width: 40%;
                    }
                    .deslocamentos tr {
                        line-height: 0.6rem!important;
                        height: 0.6rem!important;
                        font-size: 12px;
                    }
                    .deslocamentos tr td {
                        border: .8px solid #395577;
                        padding-top: .4rem!important;
                        padding-bottom: .4rem!important;
                    }
                    .container-footer {
                        width: 100%;
                        font-size: 12px;
                        text-align: center;
                        display: inline-flex!important;
                        flex-direction: column;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .footer-pdf .page-number:after { content: counter(page); }
                </style>
            </head>
            <body>
                <img src="'.$base64.'" style="width:100%;margin-top:-225px">
                <div class="content-pdf">
                    '.$html_content.'
                    <div class="footer-pdf">
                        <div class="container-footer">
                            <i>Data de Emissão '.date_format(new DateTime($estimate_info->estimate_date), 'd/m/Y').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</i>
                            <i class="page-number">Página </i>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ';

        // <div class="footer-pdf">
        // <span class="page-number">Page </span>
        // </div>
        // <div class="header-pdf">
        // <img src="'.$base64.'" alt="Cabeçalho">
        // </div>

        // Carrega o HTML com cabeçalho e rodapé no Dompdf
        $dompdf->loadHtml($html_with_header_footer);

        // Define o tamanho e orientação do papel
        $dompdf->setPaper('A4', 'portrait');

        // Renderiza o PDF
        $dompdf->render();

        // Salva o PDF em um arquivo
        $temp_file_path = get_setting("temp_file_path");
        $target_path = getcwd() . '/' . $temp_file_path;
        $target_file = $target_path . 'Proposta_' . $estimate_info->id . '.pdf';

        file_put_contents( $target_file , $dompdf->output());

        
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_id'] = $estimate_id;
        $view_data['show_js'] = $js;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);

        return view("estimates/estimate_download_pdf_direct", $view_data);
    }

    //update estimate status
    function update_estimate_status($estimate_id, $public_key, $status) {
        validate_numeric_value($estimate_id);
        if (!($estimate_id && $public_key && $status)) {
            show_404();
        }

        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!($estimate_info->id && $estimate_info->public_key === $public_key)) {
            show_404();
        }

        //client can only update the status once and the value should be either accepted or declined
        if ($status == "accepted" || $status == "declined" || $status == "in_revision" || $status == "sent") {
          
            $estimate_data = array("status" => $status);
            
            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //create notification
            if ($status == "accepted") {
            
                $status_pt = 'Aprovada';
                $data["custom_field_id"] = 1;
                $data['value'] = $status_pt;
                $data['related_to_type'] = 'estimates';
                $data['related_to_id'] = $estimate_id;

                $this->Custom_field_values_model->upsert($data, "");

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

                $this->session->setFlashdata("success_message", app_lang("estimate_accepted"));

            } else if ($status == "declined") {

                $status_pt = 'Recusada';
                $data["custom_field_id"] = 1;
                $data['value'] = $status_pt;
                $data['related_to_type'] = 'estimates';
                $data['related_to_id'] = $estimate_id;

                $this->Custom_field_values_model->upsert($data, "");

                log_notification("estimate_rejected", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("error_message", app_lang('estimate_rejected'));
            }
        }
    }

    function accept_estimate_modal_form($estimate_id = 0, $public_key = "") {
        validate_numeric_value($estimate_id);
        if (!$estimate_id) {
            show_404();
        }

        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }

        if ($public_key) {
            //public estimate
            if ($estimate_info->public_key !== $public_key) {
                show_404();
            }

            $view_data["show_info_fields"] = true;
        } else {
            //estimate preview, should be logged in client contact
            $this->access_only_clients();
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
                show_404();
            }

            $view_data["show_info_fields"] = false;
        }

        $view_data["model_info"] = $estimate_info;
        return $this->template->view('estimates/accept_estimate_modal_form', $view_data);
    }

    function accept_estimate() {
        $validation_array = array(
            "id" => "numeric|required",
            "public_key" => "required"
        );

        if (get_setting("add_signature_option_on_accepting_estimate")) {
            $validation_array["signature"] = "required";
        }

        $this->validate_submitted_data($validation_array);

        $estimate_id = $this->request->getPost("id");
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }

        $public_key = $this->request->getPost("public_key");
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $signature = $this->request->getPost("signature");

        $meta_data = array();
        $estimate_data = array();

        if ($signature) {
            $signature = explode(",", $signature);
            $signature = get_array_value($signature, 1);
            $signature = base64_decode($signature);
            $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "estimate", NULL, "", $signature));

            $meta_data["signature"] = $signature;
        }

        if ($name) {
            //from public estimate
            if (!$email) {
                show_404();
            }

            $meta_data["name"] = $name;
            $meta_data["email"] = $email;
        } else {
            //from preview, should be logged in client contact
            $this->init_permission_checker("estimate");
            $this->access_only_allowed_members_or_client_contact($estimate_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
                show_404();
            }

            $estimate_data["accepted_by"] = $this->login_user->id;
        }

        $estimate_data["meta_data"] = serialize($meta_data);
        $estimate_data["status"] = "accepted";

        if ($this->Estimates_model->ci_save($estimate_data, $estimate_id)) {
            

            $status_pt = 'Aprovada';
            $data["custom_field_id"] = 1;
            $data['value'] = $status_pt;
            $data['related_to_type'] = 'estimates';
            $data['related_to_id'] = $estimate_id;

            $this->Custom_field_values_model->upsert($data, "");
            
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
            
            echo json_encode(array("success" => true, "message" => app_lang("estimate_accepted")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("error_occurred")));
        }
    }

    /* create new project from accepted estimate */

    private function _create_project_from_estimate($estimate_id) {
        if ($estimate_id) {
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


    function reject_estimate_modal_form($estimate_id = 0, $public_key = "") {
        validate_numeric_value($estimate_id);
        if (!$estimate_id) {
            show_404();
        }

        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }

        if ($public_key) {
            //public estimate
            if ($estimate_info->public_key !== $public_key) {
                show_404();
            }

            $view_data["show_info_fields"] = true;
        } else {
            //estimate preview, should be logged in client contact
            // $this->access_only_clients();
            // if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
            //     show_404();
            // }

            $view_data["show_info_fields"] = false;
        }

        $view_data["model_info"] = $estimate_info;
        return $this->template->view('estimates/reject_estimate_modal_form', $view_data);
    }

    function reject_estimate() {
        $validation_array = array(
            "id" => "numeric|required"
        );

        $this->validate_submitted_data($validation_array);

        $estimate_id = $this->request->getPost("id");
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }
        // $public_key = $this->request->getPost("public_key");
        // if ($estimate_info->public_key !== $public_key) {
        //     show_404();
        // }

        $reason = $this->request->getPost("rejected_reason");
        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");

        $meta_data = array();
        $estimate_data = array();

        if ($name) {
            //from public estimate
            if (!$email) {
                show_404();
            }

            $meta_data["name"] = $name;
            $meta_data["email"] = $email;
            $estimate_data["rejected_by"] = 0;
        } else {
            //from preview, should be logged in client contact
            $this->init_permission_checker("estimate");
            $this->access_only_allowed_members_or_client_contact($estimate_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
                show_404();
            }

            $estimate_data["rejected_by"] = $this->login_user->id;
        }

        $estimate_data["rejected_reason"] = $reason;
        $estimate_data["meta_data"] = serialize($meta_data);
        $estimate_data["status"] = "declined";

        if ($this->Estimates_model->ci_save($estimate_data, $estimate_id)) {
            
            $status_pt = 'Recusada';
            $data["custom_field_id"] = 1;
            $data['value'] = $status_pt;
            $data['related_to_type'] = 'estimates';
            $data['related_to_id'] = $estimate_id;

            $this->Custom_field_values_model->upsert($data, "");

            log_notification("estimate_rejected", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
            echo json_encode(array("success" => true, "message" => app_lang("estimate_declined")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("error_occurred")));
        }
    }

    function delete_file($estimate_id = 0, $file) {
        if (!$estimate_id) {
            show_404();
        }
        $path = 'files/timeline_files/estimates/' . $estimate_id . '/' . $file;
        delete_file_from_directory($path); //delete temp file
        $this->session->setFlashdata("success", app_lang('estimate_deleted'));
    }

}

/* End of file Estimate.php */
/* Location: ./app/controllers/Estimate.php */