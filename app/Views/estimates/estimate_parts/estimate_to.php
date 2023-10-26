<?php if (get_setting("invoice_style") != "style_3") { ?>
    <div class="b-b" style="line-height: 2px; border-bottom: 1px solid #f2f4f6;"> </div>
<?php } ?>
<div style="line-height: 3px;color: #17365D;font-size: 18px;"> </div>
<strong color="#17365D" style="font-size: 18px;"><?php echo app_lang('client') . ': ' . $client_info->company_name; ?> </strong>
<div style="line-height: 3px;"> </div>
<span class="invoice-meta text-default" style="color: #17365D;">
    <?php if ($client_info->address) { ?>
        <div><?php echo app_lang('Address') . ': ' . nl2br($client_info->address); ?>
            <?php if ($client_info->city) { ?>
                 - <?php echo $client_info->city; ?>
            <?php } ?>
            <?php if ($client_info->state) { ?>
                , <?php echo $client_info->state; ?>
            <?php } ?>
            <?php if ($client_info->zip) { ?>
                <?php echo $client_info->zip; ?>
            <?php } ?>
            <?php if ($client_info->country) { ?>
                <br /><?php echo $client_info->country; ?>
            <?php } ?>
            <?php if ($client_info->cnpj || $client_info->gst_number) { ?>
                <?php if ($client_info->cnpj) { ?>
                    <br /><?php echo app_lang("cnpj") . ": " . $client_info->cnpj; ?>
                <?php } else { ?>
                    <br /><?php echo app_lang("gst_number") . ": " . $client_info->gst_number; ?>
                <?php } ?>
            <?php } ?>  
        </div>
    <?php } ?>
</span>