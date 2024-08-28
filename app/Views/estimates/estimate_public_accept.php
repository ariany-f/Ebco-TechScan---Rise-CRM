<!DOCTYPE html>
<html lang="en">
    <?php if((!isset($show_acceptance)) || $show_acceptance): ?>
        <head>
            <?php echo view('includes/head'); ?>
        </head>
    <?php else: ?>
        <head>
            <title>Proposta</title>
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
                            <div class="col-6"> 
                                <img class="dashboard-image float-start" src="https://uniebco.com.br/crm/files/system/_file64db9d66b25e1-site-logo.png">
                            </div>
                            <div class="col-6 d-flex" style="justify-content: end;">
                                <div class="page-title clearfix d-flex" style="align-items: center;">
                                    <h1 align="text-right" style="font-weight: 400;"><small style="font-size: .9rem;">#<?= $estimate_id ?></small> - Proposta <b><?= $estimate_info->estimate_number ?></b></h1> 
                                    <span class="badge" style="margin-top:0;font-size:15px;height: 2rem;display: flex;align-items:center;color:white;background-color:<?= $estimate_info->status == 'declined' ? 'red' : 'green'?>"><?= app_lang($estimate_info->status) ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="page-title clearfix">
                                    <h1 style="font-weight: 300;font-size: 2rem;">Informações da proposta</h1>
                                </div>
                            </div>
                            <div class="col-6 d-flex" style="justify-content: end;">
                                <?php
                                    $files1 = is_dir("./files/timeline_files/estimates/" . $estimate_info->id) ? preg_grep('~\.(pdf)$~', scandir("./files/timeline_files/estimates/" . $estimate_info->id)) : [];
                                ?>
                                <div style="display: flex;justify-content: right;align-items: center;height: 100%;">
                                    <?php foreach($files1 as $file): ?>
                                        <?php if($file!= 'index.html' && isset($file) && $file != '..' && $file != '.' && !empty(trim($file))): ?>
                                            <?php echo anchor(get_uri("files/timeline_files/estimates/" . $estimate_info->id . '/' . $file), "<i data-feather='download' class='icon-16'></i> " . $file, array("title" => app_lang('download') . ' ' . $file, "target" => "_blank", "class" => "btn mr5 p-2 mt-1", "style" => "border: 1px solid lightgrey;border-radius: 5px;width: 350px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;")); ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="d-flex">
                        <div class="col-6" style="padding: 0 25px;">
                            <?php if (isset($estimate_info->custom_fields) && $estimate_info->custom_fields) {
                                foreach ($estimate_info->custom_fields as $field) {
                                    if($field->title !== "Termômetro" && $field->title !== "Valor Estimado")
                                    {
                                        if ($field->value) {
                                            echo "<span style='display: flex;flex-direction: column;gap: 3px;'>" . $field->title . ": <b style='font-size:1rem;padding-top:5px;'>" . $field->value . "</b></span><br />";
                                        }
                                    }
                                }
                            }

                            echo "<span style='display: flex;flex-direction: column;gap: 3px;'>" . app_lang("estimate_date") . ": <b style='font-size:1rem;padding-top:5px;'>" . format_to_date($estimate_info->estimate_date, false) . '</b></span><br />'; ?><?php 
                            echo "<span style='display: flex;flex-direction: column;gap: 3px;'>" . app_lang("valid_until") . ": <b style='font-size:1rem;padding-top:5px;'>" . format_to_date($estimate_info->valid_until, false) . '</b></span>'; ?>
                            
                        </div>
                        <hr class="vertical">
                        <div class="col-6" style="display: flex;flex-direction: column;justify-content: space-between;padding: 0 25px;">
                            <div class="row mb-3 p-3 w-100">
                                <div class="col-12">
                                    <h1 style="font-weight: 300;font-size: 1rem;">Deseja <?= $estimate_info->status == 'accepted' ? 'recusar' : 'aprovar'?> a proposta?</h1>
                                </div>
                            </div>
                            <div id="controls-estimate" class = "card no-border">
                                <?php echo form_open(get_uri("estimate/" . ($estimate_info->status == 'accepted' ? 'reject' : 'accept') . "_estimate"), array("id" => "accept-estimate-form", "class" => "general-form", "role" => "form")); ?>
                                    <div class="modal-body clearfix">
                                        <div class="container-fluid p-0">
                                            <input type="hidden" name="id" value="<?php echo $estimate_id; ?>" />
                                            <input type="hidden" name="public_key" value="<?php echo $public_key; ?>" />

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
                                    <div class="modal-footer p-0">
                                        <?php if($estmate_info->status != 'accepted' and $estimate_info->status != 'declined') : ?>
                                        <button type="button" class="btn btn-default" id="reject" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('reject'); ?></button>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang($estimate_info->status == 'accepted' ? 'reject' : 'accept'); ?></button>
                                    </div>
                                    <?php echo form_close(); ?>

                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            $("#reject").on('click', function() {
                                                $('#accept-estimate-form').attr('action','<?php echo get_uri("estimate/reject_estimate") ?>');
                                                $('#accept-estimate-form').submit();
                                            })
                                            // $("#accept").on('click', function() {
                                            //     $('#accept-estimate-form').attr('action','<?php //echo get_uri("estimate/accept_estimate") ?>');
                                            // })
                                            $("#accept-estimate-form").appForm({
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
                            <div class="d-flex" style="align-items: end;justify-content: end;flex-direction: column;">
                                <?php echo company_widget($estimate_info->company_id, "estimate") ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>           
        </div>
    </body>
</html>










