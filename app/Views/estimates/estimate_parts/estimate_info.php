

<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php 
if (isset($estimate_info->custom_fields) && $estimate_info->custom_fields) {
    foreach ($estimate_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}

echo app_lang("estimate_date") . ": " . format_to_date($estimate_info->estimate_date, false); ?><br /><?php 
echo app_lang("valid_until") . ": " . format_to_date($estimate_info->valid_until, false); ?>
</span>