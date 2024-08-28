<!DOCTYPE html>
<html lang="en">
    <?php if((!isset($show_acceptance)) || $show_acceptance): ?>
        <head>
            <?php echo view('includes/head'); ?>
        </head>
    <?php else: ?>
        <head>
        <title>Proposta</title>
        <style>
            /* .card {
                transition: all 0s !important;
            }

            .mt4{
                margin-top: 4px;
            }

            .timeline-images {
                display: block;
                clear: both;
                page-break-before: always;
                page-break-after: always;
                page-break-inside: avoid;
                
            }
            .timeline-images img {
                display: block;
                clear: both;
                page-break-before: always;
                page-break-inside: avoid;
            }*/
            .invoice-preview-container > div > img {
                width: 100%;
                max-width: 95%;
            }
            .invoice-preview-container img {
                max-width: 95%;
            }
            .invoice-preview, #estimate-preview-scrollbar, #page-content {
                max-width: 810px;
                margin: 0 auto;
            }
            .invoice-preview table tr {
                width: 100%;
            }
            .invoice-preview table {
                width: 100%;
            }
            /* 
            .invoice-preview td, .invoice-preview th {
                padding: 6px 10px;
            }
            .invoice-preview *:not(.badge) {
                line-height: 20px !important;
            }
            .invoice-meta {
                font-size: 100% !important;
            }
            .print-invoice .invoice-preview-container {
                border: 1px solid #dadada;
            } */ 
            html, body, div, span, applet, object, iframe,
            h1, h2, h3, h4, h5, h6, p, blockquote, pre,
            a, abbr, acronym, address, big, cite, code,
            del, dfn, em, img, ins, kbd, q, s, samp,
            small, strike, strong, sub, sup, tt, var,
            b, u, i, center,
            dl, dt, dd, ol, ul, li,
            fieldset, form, label, legend,
            table, caption, tbody, tfoot, thead, tr, th, td,
            article, aside, canvas, details, embed, 
            figure, figcaption, footer, header, hgroup, 
            menu, nav, output, ruby, section, summary,
            time, mark, audio, video {
                margin: 0;
                padding: 0;
                border: 0;
                font-size: 100%;
                font: inherit;
                vertical-align: baseline;
            }
            /* HTML5 display-role reset for older browsers */
            article, aside, details, figcaption, figure, 
            footer, header, hgroup, menu, nav, section {
                display: block;
            }
            body {
                line-height: 1;
            }
            ol, ul {
                list-style: none;
            }
            blockquote, q {
                quotes: none;
            }
            blockquote:before, blockquote:after,
            q:before, q:after {
                content: '';
                content: none;
            }
            table {
                border-collapse: collapse;
                border-spacing: 0;
            }

            /** Personalizado */

            @import url('https://fonts.googleapis.com/css?family=Helvetica:300,300i,400,500,600,700,700i,800');

            :root {
                --primary-color: #1f497d;
                --secondary-table-color: #cacfe1;
            }
            body {
                font-family: "Helvetica", sans-serif;
                font-weight:400;
                margin: 0 auto;
                color: var(--primary-color);
                font-size: 12px;
                line-height: 14px;
            }
            body b {
                font-weight: 700;
            }
            body table {
                max-width: 900px;
                min-width: 400px;
                margin: 0 auto;
                word-spacing: 2px;
            }
            body table td{
                box-sizing: border-box;
                max-width: 100%;
                margin: 0 auto;
            }
            table tr {
                text-align: center;
            }
            h1, h2, h3, h4, h5, h6 {
                font-weight:700;
            }
            table tr:not(.header) td {
                padding-top: .8rem;
                padding-bottom: .8rem;
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
            }
            table .content td{
                padding-top: 0!important;
                color: rgb(70, 70, 70);
            }
            table .assinatura img{
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
                background-color: var(--primary-color); 
                color: #fff;
            }
            table.items .table-header td{
                border: .8px solid #395577!important;
            }
            table.items .table-sum {
                background-color: var(--secondary-table-color); 
                line-height: 20px;
                font-size: 14px;
                margin-bottom: 20px;
            }
            table.items .table-content {
                line-height: 20px;
                font-size: 14px;
            }
            .page-break {
                page-break-inside: avoid;
                page-break-after: always;
            }
            /* .page-break>td {
                padding: 0!important;
            } */ */
        </style>
        </head>
    <?php endif; ?>
    <body>
        
        <div id="estimate-preview-scrollbar">
            
            <div id="page-content" class="page-wrapper clearfix">
                
                <?php
                    
                    if((!isset($show_acceptance)) || $show_acceptance) {
                        load_css(array(
                            "assets/css/invoice.css",
                            "assets/css/estimate.css",
                        ));
                        load_js(array(
                            "assets/js/signature/signature_pad.min.js",
                        ));
                    }
                ?>

                <div class="invoice-preview estimate-preview">
                    <div class="card p-15  no-border">
                        <div class="row mb-2">
                            <div class="col-3"> 
                                <img class="dashboard-image float-start" src="https://uniebco.com.br/crm/files/system/_file64db9d66b25e1-site-logo.png">
                            </div>
                            <div class="col-6">
                                <div class="page-title clearfix">
                                    <h1 align="center">Proposta #<?= $estimate_id ?></h1>
                                </div>
                            </div>
                            <div class="col-3">
                                <?php
                                    $files1 = is_dir("./files/timeline_files/estimates/" . $estimate_info->id) ? preg_grep('~\.(pdf)$~', scandir("./files/timeline_files/estimates/" . $estimate_info->id)) : [];
                                ?>
                                <div style="display: flex;justify-content: right;align-items: center;height: 100%;">
                                    <?php foreach($files1 as $file): ?>
                                        <?php if($file!= 'index.html' && isset($file) && $file != '..' && $file != '.' && !empty(trim($file))): ?>
                                            <?php echo anchor(get_uri("files/timeline_files/estimates/" . $estimate_info->id . '/' . $file), "<i data-feather='download' class='icon-16'></i> " . $file, array("title" => app_lang('download') . ' ' . $file, "target" => "_blank", "class" => "btn mr5 p-2 mt-5", "style" => "border: 1px solid lightgrey;border-radius: 5px;width: 250px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;")); ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3 p-3 w-100">
                            <div class="col-12">
                                <div class="mt5 bg-transparent-white"><b>Deseja rejeitar a proposta?</b></div>
                            </div>
                        </div>
                    </div>
                    <div id="controls-estimate" class = "card p15 no-border">
                        <?php echo form_open(get_uri("estimate/reject_estimate"), array("id" => "reject-estimate-form", "class" => "general-form", "role" => "form")); ?>
                            <div class="modal-body clearfix">
                                <div class="container-fluid">
                                    <input type="hidden" name="id" value="<?php echo $estimate_id; ?>" />
                                    <input type="hidden" name="public_key" value="<?php echo $public_key; ?>" />

                                    <?php if ($show_info_fields) { ?>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="name" class=" col-md-3"><?php echo app_lang('name'); ?></label>
                                                <div class="col-md-9">
                                                    <?php
                                                    echo form_input(array(
                                                        "id" => "name",
                                                        "name" => "name",
                                                        "class" => "form-control",
                                                        "placeholder" => app_lang('name'),
                                                        "data-rule-required" => true,
                                                        "data-msg-required" => app_lang("field_required"),
                                                    ));
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="email" class="col-md-3"><?php echo app_lang('email'); ?></label>
                                                <div class="col-md-9">
                                                    <?php
                                                    echo form_input(array(
                                                        "id" => "email",
                                                        "name" => "email",
                                                        "class" => "form-control",
                                                        "placeholder" => app_lang('email'),
                                                        "data-rule-email" => true,
                                                        "data-msg-email" => app_lang("enter_valid_email"),
                                                        "data-rule-required" => true,
                                                        "data-msg-required" => app_lang("field_required"),
                                                    ));
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if (get_setting("add_signature_option_on_accepting_estimate")) { ?>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="signature" class="col-md-3"><?php echo app_lang('signature'); ?></label>
                                                <div class="col-md-9">
                                                    <div id="signature">
                                                        <canvas class="b-a" height="200"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <?php if ($show_info_fields) { ?>
                                    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
                                    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('reject'); ?></button>
                                <?php } else { ?>
                                    <div class="card-body">
                                        <div class="widget-icon bg-primary ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle icon-16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        </div>
                                        <div class="widget-details">
                                            <span class="badge bg-primary">
                                                <h1 style="font-size:15px;color:white;">Proposta rejeitada!</h1>
                                            </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php echo form_close(); ?>

                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $("#reject-estimate-form").appForm({
                                        onSuccess: function (result) {
                                            if (result.success) {
                                                appAlert.success(result.message, {duration: 10000});
                                                setTimeout(function () {
                                                    location.reload();
                                                }, 1000);
                                            } else {
                                                appAlert.error(result.message);
                                            }
                                        }
                                    });

                                    $("#name").focus();

                                    initSignature("signature", {
                                        required: true,
                                        requiredMessage: "<?php echo app_lang("field_required"); ?>"
                                    });
                                });
                            </script>
                    </div>
                   
                </div>
            </div>           
        </div>
    </body>
</html>










