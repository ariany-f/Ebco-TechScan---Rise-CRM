<?php echo form_open(get_uri("estimate_templates/save"), array("id" => "estimate_template-form", "class" => "general-form", "estimate_template" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control",
                        "placeholder" => app_lang('title'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <?php if (!$model_info->id) { ?>
                    <label for="copy_template" class=" col-md-3"><?php echo app_lang('use_template_from'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_dropdown("copy_template", $estimate_templates_dropdown, "", "class='select2' id='copy_template' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                        ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#estimate_template-form").appForm({
            onSuccess: function (result) {
                $("#estimate_template-table").appTable({newData: result.data, dataId: result.id});
            }
        });
        $("#copy_template").select2();
        setTimeout(function () {
            $("#title").focus();
        }, 200);
    });
</script>