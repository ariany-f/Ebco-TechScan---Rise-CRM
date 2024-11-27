<?php echo form_open(get_uri("team_members/migrate_data"), array("id" => "team-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="member_id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <div class=" col-md-9">
                    <?php
                        echo form_checkbox("transfer_estimates", "1", true, "id='transfer_estimates' class='form-check-input'");
                        ?> <label for="transfer_estimates"><?php echo app_lang('transfer_estimates'); ?></label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="members" class=" col-md-3"><?php echo app_lang('for_team_member'); ?></label>
                <div class="col-md-9">
                    <input type="text" value="<?php echo $model_info->members; ?>" name="member" id="team_members_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>" placeholder="<?php echo app_lang('team_member'); ?>"  />    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('transfer'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#team-form").appForm({
            onSuccess: function (result) {
                $("#team-table").appTable({newData: result.data, dataId: result.id});
            }
        }); 

        $("#team_members_dropdown").select2({
            data: <?php echo ($members_dropdown); ?>
        });

        $("#team-form .select2").select2();
        setTimeout(function () {
            $("#title").focus();
        }, 200);
    });
</script>    