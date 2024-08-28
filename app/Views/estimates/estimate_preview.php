<div class="box h-100">
    <div class="box-content">
        <div id="estimate-preview-content" class="page-wrapper clearfix">
            <?php
            load_css(array(
                "assets/css/invoice.css",
                "assets/css/estimate.css",
            ));

            load_js(array(
                "assets/js/signature/signature_pad.min.js",
            ));
            ?>

            <div class="invoice-preview estimate-preview">
                
                <?php if (!isset($is_editor_preview) && ((!isset($buttons)) || $buttons)) {
                    $action_buttons = "<div class='clearfix float-end'>";

                    if ($show_close_preview) {
                        echo "<div class='text-center'>" . anchor("estimates/view/" . $estimate_info->id, app_lang("close_preview"), array("class" => "btn btn-default round mb20 mr5")) . "</div>";
                    }

                    $action_buttons .= "<div class='float-start'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_estimate'), array('id' => 'print-estimate-btn', "class" => "btn btn-default round mr10")) . "</div>";

                    if ($login_user->user_type === "staff") {
                        $action_buttons .= "<div class='float-start'>" . anchor(get_uri("estimate/preview/" . $estimate_info->id . "/" . $estimate_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('estimate') . " " . app_lang("url"), array("class" => "btn btn-default round mr5")) . "</div>";
                    }

                    $action_buttons .= "</div>";

                    if ($show_acceptance && ($estimate_info->status === "accepted" || $estimate_info->status === "declined" || $estimate_info->status === "rejected")) {
                        ?>
                        <div id="controls-estimate" class = "card  p15 no-border">
                            <div class="clearfix">
                                <div class="float-start mt5">
                                    <?php if ($estimate_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("estimate_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("estimate_rejected"); ?>
                                    <?php } ?>
                                </div>

                                <?php echo $action_buttons; ?>
                            </div>
                        </div>
                        <?php
                    } else if((!isset($show_acceptance)) || $show_acceptance) {
                        if ($login_user->user_type === "client" && $estimate_info->status == "new") {
                            ?>
        
                            <div id="controls-estimate" class = "card  p15 no-border clearfix inline-block w100p mb0">
        
                                <div class="mr15 strong float-start">
                                    <?php
                                    if (get_setting("add_signature_option_on_accepting_estimate")) {
                                        echo modal_anchor(get_uri("estimate/accept_estimate_modal_form/$estimate_info->id"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => app_lang('accept_estimate')));
                                    } else {
                                        echo ajax_anchor(get_uri("estimates/update_estimate_status/$estimate_info->id/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => app_lang('mark_as_accepted'), "data-reload-on-success" => "1"));
                                    }
                                    ?>
                                    <?php echo ajax_anchor(get_uri("estimates/update_estimate_status/$estimate_info->id/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("class" => "btn btn-danger mr15", "title" => app_lang('mark_as_rejected'), "data-reload-on-success" => "1")); ?>
                                </div>
                                <div class="float-end">
                                    <?php
                                    echo "<div class='text-center'>" . anchor("estimates/download_pdf/" . $estimate_info->id, app_lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
                                    ?>
                                </div>
        
                            </div>
        
                            <?php
                        } else if ($login_user->user_type === "client") {
                            echo "<div class='float-start'>" . anchor("estimates/download_pdf/" . $estimate_info->id, app_lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
                        }
                    }
                }
                ?>

            <div class="invoice-preview">
                <?php if((!isset($buttons)) || $buttons): ?>
                    <div id="estimate-preview" class="invoice-preview-container bg-white mt15">
                        <div class="row">
                            <div class="col-md-12 position-relative">
                                <div class="ribbon"><?php echo $estimate_status_label; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if(!$show_acceptance || $show_acceptance === 0) : ?>
                    <?php echo $estimate_preview; ?>
                <?php endif; ?>
                <div id="marker"></div>
            </div>
            </div>
        </div>
    </div>

    <?php if (get_setting("enable_comments_on_estimates") && $estimate_info->status != "draft") { ?>
        <div class="hidden-xs box-content bg-white" style="width: 400px; min-height: 100%;">
            <div id="estimate-comment-container">
                <?php echo view("estimates/comment_form"); ?>
            </div>
        </div>
    <?php } ?>
</div>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->
<?php if((!isset($show_acceptance)) || $show_acceptance): ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#payment-amount").change(function () {
            var value = $(this).val();
            $(".payment-amount-field").each(function () {
                $(this).val(value);
            });
        });
        
        //reload page after finishing print action
        window.onafterprint = function () {
            location.reload();
        };
    });
</script>
<?php endif; ?>