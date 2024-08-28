<?php echo form_open(get_uri("estimate/reject_estimate"), array("id" => "accept-estimate-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

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

        <div class="form-group">
                <div class="row">
                    <label for="rejected_reason" class=" col-md-3"><?php echo app_lang('rejected_reason'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_textarea(array(
                            "id" => "rejected_reason",
                            "name" => "rejected_reason",
                            "class" => "form-control",
                            "placeholder" => app_lang('rejected_reason'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('reject'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#accept-estimate-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                    location.reload();
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