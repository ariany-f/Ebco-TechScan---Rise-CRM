<?php
$uid = "_" . uniqid(rand());

$options = $field_info->options ? $field_info->options : "";
$options_array = explode(",", $options);

$options_dropdown = array();
if ($options && count($options_array)) {
    foreach ($options_array as $value) {
        if (strpos($value, ':') !== false) {
            $value_array = explode(":", $value);
            $id = $value_array[0];
            $name = $value_array[1];
        }
        else
        {
            $id = $value;
            $name = $value;
        }
        $options_dropdown[] = array("id" => $id, "text" => $name);
    }
} else {
    $options_dropdown = array(array("id" => "-", "text" => "-"));
}

echo form_input(array(
    "id" => "custom_field_" . $field_info->id . $uid,
    "name" => "custom_field_" . $field_info->id,
    "value" => isset($field_info->value) ? $field_info->value : "",
    "class" => "form-control validate-hidden",
    "placeholder" => $placeholder,
    "data-rule-required" => $field_info->required ? true : "false",
    "data-msg-required" => app_lang("field_required"),
    "data-custom-multi-select-input" => 1
));
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#custom_field_<?php echo $field_info->id . $uid; ?>").select2({data:<?php echo json_encode($options_dropdown); ?>, tags: true});
    });
</script>
