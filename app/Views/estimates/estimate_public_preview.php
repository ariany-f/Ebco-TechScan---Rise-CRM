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
                    <div id="controls-estimate" class = "card  p15 no-border">
                        <div class="clearfix">
                            <input type="hidden" id="estimate_date" value="<?= format_to_date($estimate_info->estimate_date, false) ?>">
                            <?php
                                $files1 = is_dir("./files/timeline_files/estimates/" . $estimate_info->id) ? preg_grep('~\.(pdf)$~', scandir("./files/timeline_files/estimates/" . $estimate_info->id)) : [];
                            ?>
                            <?php if ($estimate_info->status === "accepted" || $estimate_info->status === "declined" || $estimate_info->status === "rejected") { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="float-end mt10 mr15">
                                    <?php if ($estimate_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("estimate_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("estimate_rejected"); ?>
                                    <?php } ?>
                                </div>
                            <?php } else if((!isset($show_acceptance)) || $show_acceptance) { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="strong float-end mt4">
                                    <!-- <?php //echo ajax_anchor(get_uri("estimate/update_estimate_status/$estimate_info->id/$estimate_info->public_key/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject_estimate'), array("class" => "btn btn-danger mr10", "title" => app_lang('reject_estimate'), "data-reload-on-success" => "1")); ?> -->
                                    <?php echo modal_anchor(get_uri("estimate/reject_estimate_modal_form/$estimate_info->id/$estimate_info->public_key"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject_estimate'), array("class" => "btn btn-danger mr5", "title" => app_lang('reject_estimate'))); ?>
                                    <?php echo modal_anchor(get_uri("estimate/accept_estimate_modal_form/$estimate_info->id/$estimate_info->public_key"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept_estimate'), array("class" => "btn btn-success mr5", "title" => app_lang('approve_estimate'))); ?>
                                    <!-- <?php //echo anchor(get_uri("estimate/download_pdf/" . $estimate_info->id . "/" . $estimate_info->public_key), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "target" => "_blank",  "class" => "btn btn-primary mr5")); ?> -->
                                </div>
                            <?php } ?>
                        </div>
                        <div style="display: flex;width: 100%;flex-wrap: wrap;">
                            <?php foreach($files1 as $file): ?>
                                <?php if($file!= 'index.html' && isset($file) && $file != '..' && $file != '.' && !empty(trim($file))): ?>
                                    <?php echo anchor(get_uri("files/timeline_files/estimates/" . $estimate_info->id . '/' . $file), "<i data-feather='download' class='icon-16'></i> " . $file, array("title" => app_lang('download') . ': ' . $file , "target" => "_blank",  "class" => "btn mr5 p-2 mt-5", "style" => "border: 1px solid lightgrey;border-radius: 5px;width: 200px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;")); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="invoice-preview-container bg-white">
                        <?php if((isset($show_acceptance)) || !$show_acceptance): ?>
                            <?php echo $estimate_preview; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if((!isset($show_acceptance)) || $show_acceptance): ?>
                <?php echo view('modal/index'); ?>
            <?php endif; ?>
        </div>
       
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->
        <?php if((!isset($show_acceptance)) || $show_acceptance): ?>
            <script>
                $(document).ready(function () {
                    initScrollbar('#estimate-preview-scrollbar', {
                        setHeight: $(window).height()
                    });

                    $("#custom-theme-color").remove();

                });
            </script>
        <?php endif; ?>
    </body>
</html>










