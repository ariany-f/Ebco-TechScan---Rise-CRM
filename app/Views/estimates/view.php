<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt25">
            <h1><strong><?php echo get_estimate_id($estimate_info->estimate_number ?? $estimate_info->parent_estimate ?? $estimate_info->estimate_number_temp); ?></strong></h1>
            <div class="title-button-group">
                <span class="dropdown inline-block mt15">
                    <button class="btn btn-info text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <!-- <li role="presentation"><?php //echo anchor(get_uri("estimates/download_pdf/" . $estimate_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "dropdown-item")); ?> </li> -->
                        <li role="presentation"><?php echo anchor(get_uri("estimate/download_pdf/" . $estimate_info->id . "/" . $estimate_info->public_key), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "target" => "_blank",  "class" => "dropdown-item")); ?> </li>
                        <!-- <li role="presentation"><?php //echo anchor(get_uri("estimates/download_pdf/" . $estimate_info->id . "/view"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('view_pdf'), array("title" => app_lang('view_pdf'), "target" => "_blank", "class" => "dropdown-item")); ?> </li> -->
                        <li role="presentation"><?php echo anchor(get_uri("estimates/preview/" . $estimate_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('estimate_preview'), array("title" => app_lang('estimate_preview'), "target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("estimate/preview/" . $estimate_info->id . "/" . $estimate_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('estimate') . " " . app_lang("url"), array("target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <!-- <li role="presentation"><?php //echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_estimate'), array('title' => app_lang('print_estimate'), 'id' => 'print-estimate-btn', "class" => "dropdown-item")); ?> </li> -->
                        <!-- <li role="presentation"><?php //echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('download_pdf'), array('title' => app_lang('download_pdf'), 'id' => 'print-estimate-btn', "class" => "dropdown-item")); ?> </li> -->
                        <li role="presentation" class="dropdown-divider"></li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_estimate'), array("title" => app_lang('edit_estimate'), "data-post-id" => $estimate_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_estimate'), array("data-post-is_clone" => true, "data-post-id" => $estimate_info->id, "title" => app_lang('clone_estimate'), "class" => "dropdown-item")); ?></li>
                        <?php
                        if ($estimate_status != "in_revision") { ?>
                            <!-- <li role="presentation"><?php //echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/in_revision"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('mark_as_revision'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li> -->
                        <?php
                        }
                        ?>
                        <?php
                        if ($estimate_status == "draft" || $estimate_status == "sent") {
                            ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <!-- <li role="presentation"><?php //echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li> -->
                            <li role="presentation"><?php echo modal_anchor(get_uri("estimate/reject_estimate_modal_form/" . $estimate_info->id), "<i data-feather='x' class='icon-16'></i> " . app_lang('mark_as_declined'), array("title" => app_lang('mark_as_declined'), "data-post-id" => $estimate_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                        <?php } else if ($estimate_status == "in_revision") {
                        ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/sent"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_sent'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } else if ($estimate_status == "accepted") {
                            ?>
                            <li role="presentation"><?php echo modal_anchor(get_uri("estimate/reject_estimate_modal_form/" . $estimate_info->id), "<i data-feather='x' class='icon-16'></i> " . app_lang('mark_as_declined'), array("title" => app_lang('mark_as_declined'), "data-post-id" => $estimate_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                            <!-- <li role="presentation"><?php //echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li> -->
                            <?php
                        } else if ($estimate_status == "declined") {
                            ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php
                        }
                        ?>
                        <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/create_revision"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('create_revision'), array("data-post-id" => $estimate_info->id, "data-reload-on-success" => 1, "class" => "dropdown-item")); ?> </li>

                        <?php
                        if ($client_info->is_lead) {
                            if ($estimate_status == "draft" || $estimate_status == "sent") {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/send_estimate_modal_form/" . $estimate_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_lead'), array("title" => app_lang('send_to_lead'), "data-post-id" => $estimate_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        } else {
                            if ($estimate_status == "draft" || $estimate_status == "sent") {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/send_estimate_modal_form/" . $estimate_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_client'), array("title" => app_lang('send_to_client'), "data-post-id" => $estimate_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        }
                        ?>

                        <?php if ($estimate_status == "accepted") { ?>
                            <li role="presentation" class="dropdown-divider"></li>
                            <?php if ($can_create_projects && !$estimate_info->project_id) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('create_project'), array("data-post-estimate_id" => $estimate_info->id, "title" => app_lang('create_project'), "data-post-client_id" => $estimate_info->client_id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                            <?php if ($show_invoice_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='refresh-cw' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-estimate_id" => $estimate_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </span>
            </div>
        </div>
        <?php if((!empty($estimate_info->rejected_reason)) && $estimate_status == 'declined') : ?>
            <?php
                $signer_info = @unserialize($estimate_info->meta_data);
                if (!($signer_info && is_array($signer_info))) {
                    $signer_info = array();
                }
                ?>

            <div class="bg-white p15 no-border m0 rounded-bottom">
                <div><strong>Motivo Rejeição: </strong><?php echo $estimate_info->rejected_reason ?></div>
            
                <?php if ($estimate_status === "declined" && ($signer_info || $estimate_info->accepted_by)) { ?>
                    <div><strong><?php echo app_lang("rejected_name"); ?>: </strong><?php echo $estimate_info->accepted_by ? get_client_contact_profile_link($estimate_info->accepted_by, $estimate_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
                    <div><strong><?php echo app_lang("rejected_email"); ?>: </strong><?php echo $estimate_info->signer_email ? $estimate_info->signer_email : get_array_value($signer_info, "email"); ?></div>
                <?php } ?>
            </div>
        <?php endif; ?>
        <div id="estimate-status-bar">
            <?php echo view("estimates/estimate_status_bar"); ?>
        </div>
        
        <div class="mt15">
            <div class="card no-border clearfix ">
                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#estimate-items"><?php echo app_lang("estimate"); ?></a></li>
                    <!-- <li><a role="presentation" data-bs-toggle="tab" href="<?php //echo_uri("estimates/editor/" . $estimate_info->id); ?>" data-bs-target="#estimate-editor"><?php //echo app_lang("estimate_editor"); ?></a></li> -->
                    <!-- <li><a role="presentation" data-bs-toggle="tab" href="<?php //echo_uri("estimates/preview/" . $estimate_info->id . "/0/1"); ?>" data-bs-target="#estimate-preview" data-reload="true"><?php //echo app_lang("preview"); ?></a></li> -->
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="estimate-items">
                        <div class="clearfix p20">
                            <!-- small font size is required to generate the pdf, overwrite that for screen -->
                            <style type="text/css">
                                .invoice-meta {
                                    font-size: 100% !important;
                                }
                            </style>

                            <?php
                            $color = get_setting("estimate_color");
                            if (!$color) {
                                $color = get_setting("invoice_color");
                            }
                            $style = get_setting("invoice_style");
                            ?>
                            <?php
                            $data = array(
                                "client_info" => $client_info,
                                "color" => $color ? $color : "#2AA384",
                                "estimate_info" => $estimate_info
                            );
                            
                            if ($style === "style_3") {
                                echo view('estimates/estimate_parts/header_style_3.php', $data);
                            } else if ($style === "style_2") {
                                echo view('estimates/estimate_parts/header_style_2.php', $data);
                            } else {
                                echo view('estimates/estimate_parts/header_style_1.php', $data);
                            }
                            ?>
                        </div>

                        <div class="table-responsive mt15 pl15 pr15">
                            <div class="col-md-12 mb-5">
                                <h4><strong><?php echo app_lang("files") ?></strong></h4>
                            </div>
                            <table id="files-estimate-table" class="display" cellspacing="0" width="100%">   
                            </table>
                        </div>


                        <div class="table-responsive mt15 pl15 pr15">
                            <div class="col-md-12 mb-5">
                                <h4><strong><?php echo app_lang("revisions") ?></strong></h4>
                            </div>
                            <table id="revisions-estimate-table" class="display" cellspacing="0" width="100%">   
                            </table>
                        </div>

                        <!-- <div class="table-responsive mt15 pl15 pr15 mt20">
                            <table id="estimate-item-table" class="display" width="100%">            
                            </table>
                        </div> -->

                        <!-- <div class="clearfix">
                            <div class="float-start mt20 ml15">
                                <?php //echo modal_anchor(get_uri("estimates/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-info text-white", "title" => app_lang('add_item'), "data-post-estimate_id" => $estimate_info->id)); ?>
                            </div>
                            <div class="float-end pr15" id="estimate-total-section">
                                <?php //echo view("estimates/estimate_total_section"); ?>
                            </div>
                        </div> -->

                        <?php
                        if (get_setting("enable_comments_on_estimates") && !($estimate_info->status === "draft")) {
                            echo view("estimates/comment_form");
                        }
                        ?>

                    </div>
                    
                    <!-- <div role="tabpanel" class="tab-pane fade" id="estimate-editor"></div> -->
                    <!-- <div role="tabpanel" class="tab-pane fade" id="estimate-preview"></div> -->
                </div>
            </div>
        </div>

        <?php
        $signer_info = @unserialize($estimate_info->meta_data);
        if (!($signer_info && is_array($signer_info))) {
            $signer_info = array();
        }
        ?>
        <?php if ($estimate_status === "accepted" && ($signer_info || $estimate_info->accepted_by)) { ?>
            <div class="card mt15">
                <div class="page-title clearfix ">
                    <h1><?php echo app_lang("signer_info"); ?></h1>
                </div>
                <div class="p15">
                    <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo $estimate_info->accepted_by ? get_client_contact_profile_link($estimate_info->accepted_by, $estimate_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
                    <div><strong><?php echo app_lang("email"); ?>: </strong><?php echo $estimate_info->signer_email ? $estimate_info->signer_email : get_array_value($signer_info, "email"); ?></div>
                    <?php if (get_array_value($signer_info, "signed_date")) { ?>
                        <div><strong><?php echo app_lang("signed_date"); ?>: </strong><?php echo format_to_relative_time(get_array_value($signer_info, "signed_date")); ?></div>
                    <?php } ?>

                    <?php
                    if (get_array_value($signer_info, "signature")) {
                        $signature_file = @unserialize(get_array_value($signer_info, "signature"));
                        $signature_file_name = get_array_value($signature_file, "file_name");
                        $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
                        ?>
                        <div><strong><?php echo app_lang("signature"); ?>: </strong><br /><img class="signature-image" src="<?php echo $signature_file; ?>" alt="<?php echo $signature_file_name; ?>" /></div>
                        <?php } ?>
                </div>
            </div>
        <?php } ?>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script type="text/javascript">
    //RELOAD_VIEW_AFTER_UPDATE = true;
    $(document).ready(function () {

        $("#files-estimate-table").appTable({
            source: '<?php echo_uri("estimates/list_files_data/" . $estimate_info->id) ?>',
            order: [[0, "desc"]],
            columns: [
                {title: '<?php echo app_lang("id") ?>'},
                {title: '<?php echo app_lang("file") ?>'},
                {title: '<?php echo app_lang("size") ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });

        $("#revisions-estimate-table").appTable({
            source: '<?php echo_uri("estimates/list_revisions_data/". $estimate_info->id . "/") ?>',
            order: [[0, "desc"]],
            columns: [
                {title: "<?php echo app_lang("number") ?>", "class": "text-center"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {visible: false, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
                <?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ]
        });

        $("#estimate-item-table").appTable({
            source: '<?php echo_uri("estimates/item_list_data/" . $estimate_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("item") ?> ", "bSortable": false},
                {title: "<?php echo app_lang("quantity") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<?php echo app_lang("rate") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<?php echo app_lang("total") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100", "bSortable": false}
            ],

            onInitComplete: function () {
                //apply sortable
                $("#estimate-item-table").find("tbody").attr("id", "estimate-item-table-sortable");
                var $selector = $("#estimate-item-table-sortable");

                Sortable.create($selector[0], {
                    animation: 150,
                    chosenClass: "sortable-chosen",
                    ghostClass: "sortable-ghost",
                    onUpdate: function (e) {
                        appLoader.show();
                        //prepare sort indexes 
                        var data = "";
                        $.each($selector.find(".item-row"), function (index, ele) {
                            if (data) {
                                data += ",";
                            }

                            data += $(ele).attr("data-id") + "-" + index;
                        });

                        //update sort indexes
                        $.ajax({
                            url: '<?php echo_uri("estimates/update_item_sort_values") ?>',
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });

            },

            onDeleteSuccess: function (result) {
                $("#estimate-total-section").html(result.estimate_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.estimate_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#estimate-total-section").html(result.estimate_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.estimate_id);
                }
            }
        });

        $("body").on("click", "#estimate-save-and-show-btn", function () {
            $(this).trigger("submit");

            setTimeout(function () {
                $("[data-bs-target='#estimate-preview']").trigger("click");
            }, 400);
        });

        
        // window.jsPDF = window.jspdf.jsPDF;
        // var doc = new jsPDF();

        // const addFooters = (doc) => {
        //     const pageCount = doc.internal.getNumberOfPages()

        //     doc.setFont('helvetica', 'italic')
        //     doc.setFontSize(8)
        //     for (var i = 1; i <= pageCount; i++) {
        //         doc.setPage(i)
        //         doc.text('Data de Emissão ' + $("#estimate_date").val() + ' Página ' + String(i) + ' de ' + String(pageCount), doc.internal.pageSize.width / 2, 287, {
        //         align: 'center'
        //     })
        //     }
        // }

        //print estimate
        $("#print-estimate-btn").click(function () {
            appLoader.show();

            $.ajax({
                url: "<?php echo get_uri('estimate/print_estimate/' . $estimate_info->id . '/' . $estimate_info->public_key) ?>",
                dataType: 'json',
                data: {buttons: false},
                success: function (result) {
                    if (result.success) {
                        let div = result.print_view;
                       // document.body.innerHTML = div; //add estimate's print view to the page
                      //  document.body.innerHTML = div; //add estimate's print view to the page
                      //  let noButtonsDiv = document.body.getElementsByClassName('invoice-preview-container')[0].innerHTML;
                        
                        // doc.html(div, {
                        //     html2canvas: {
                        //     },
                        //     compress: true,
                        //     putOnlyUsedFonts: true,
                        //     orientation: 'p',
                        //     unit: 'mm',
                        //     format: 'a4',
                        //     autoPaging: 'text',
                        //     margin: [10, 0, 15 ,0], // the default is [0, 0, 0, 0]
                        //     callback: function(doc) {
                        //         // Save the PDF
                        //         addFooters(doc)
                        //         doc.save('Proposta ' + <?php //echo $estimate_info->id ?> + '.pdf');
                        //     },
                        //     x: 0,
                        //     y: 0,
                        //     width: 210, //target width in the PDF document
                        //     windowWidth: 810 //window width in CSS pixels
                        // });

                     //   const noButtonsDiv = document.querySelector('#my-table');
                               
                        //window.devicePixelRatio = 5;
                        const options = {
                            margin: [45, 15, 15, 25],
                            filename: 'Proposta <?php echo $estimate_info->id ?>.pdf',
                            pagebreak: { mode: ['avoid-all', 'css', 'legacy'], avoid: 'img'},
                            html2canvas: {scale: 2, letterRendering: true},
                            jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true, format: 'letter', compressPDF: true, pagesplit: true}
                        };
                        //.save() Download PDF
                        html2pdf().set(options).from(div).toPdf().get('pdf').then((pdf) => {
                            
                            
                            const e = pdf.internal.collections.addImage_images;
                            console.log(e)
                            for (let i in e) {
                                e[i].height <= 20 ? pdf.deletePage(+i + 1) : null;
                            }
                            
                            // handle your result here...
                            var totalPages = pdf.internal.getNumberOfPages();


                            for (let i = 1; i <= totalPages; i++) {
                                // set footer to every page
                                pdf.setPage(i);
                                // set footer font
                                //pdf.setFont('helvetica', 'normal')
                                pdf.setFontSize(10);
                                pdf.setTextColor(0, 0, 0);
                                var img = new Image()
                                img.src = "<?php echo get_uri('assets/images/header.png') ?>"
                                pdf.addImage(img, 'png', -20, -115, 850, 160)
                                
                                pdf.text(pdf.internal.pageSize.getWidth() - 80, 20, 'Página ' + String(i) + ' de ' + String(totalPages) + '', 'left');
                                
                                pdf.text(15, pdf.internal.pageSize.getHeight() - 15, 'Data de Emissão ' + $("#estimate_date").val(), 'left');
                                pdf.text(pdf.internal.pageSize.getWidth() - 80, pdf.internal.pageSize.getHeight() - 15, 'Página ' + String(i) + ' de ' + String(totalPages) + '', 'left');
                            }
                        }).outputPdf('bloburl').then((result) => {
                            window.open(result, '_blank');
                        });
                               

                    } else {
                        appAlert.error(result.message);
                    }

                    appLoader.hide();
                }
            });
        });

        //reload page after finishing print action
        window.onafterprint = function () {
            location.reload();
        };

    });


    updateInvoiceStatusBar = function (estimateId) {
        $.ajax({
            url: "<?php echo get_uri("estimates/get_estimate_status_bar"); ?>/" + estimateId,
            success: function (result) {
                if (result) {
                    $("#estimate-status-bar").html(result);
                }
            }
        });
    };

</script>

<?php
//required to send email 

load_css(array(
    "assets/js/summernote/summernote.css",
));
load_js(array(
    "assets/js/summernote/summernote.min.js", 
    "assets/js/summernote-paper-size-master/summernote-paper-size.js",
));
?>


<?php echo view("estimates/print_estimate_helper_js"); ?>
