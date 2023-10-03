<input type="checkbox" <?php echo (isset($field_info->value)) ? (($field_info->value == "On") ? "checked" : "") : ""  ?> id="custom_field_<?php echo $field_info->id ?>" name="custom_field_<?php echo $field_info->id; ?>"  placeholder="<?php echo $placeholder; ?>"  data-rule-required ='<?php echo $field_info->required ? true : "false"; ?>'  data-msg-required="<?php echo app_lang('field_required'); ?>">

<script type="text/javascript">
    $(document).ready(function () {
        $("input#custom_field_<?php echo $field_info->id; ?>").change();
    });
    
    $("input#custom_field_<?php echo $field_info->id; ?>").change(function() {
        if(this.checked) {
            $(this).prop("checked", true);
        }
        else
        {
            $(this).removeProp("checked");
        }    
    });
</script>