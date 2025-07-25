<div class="card rounded-bottom">
    <div class="tab-title clearfix">
        <h4><?php echo app_lang('estimates'); ?></h4>
        <div class="title-button-group">
            <?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_estimate'), array("class" => "btn btn-default", "data-post-client_id" => $client_id, "title" => app_lang('add_estimate'))); ?>
        </div>
    </div>
    <div class="table-responsive">
        <table id="estimate-table" class="display" width="100%">
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_estimates") == "1" ?>") {
            showCommentOption = true;
        }
        
        $("#estimate-table").appTable({
            source: '<?php echo_uri("estimates/estimate_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("estimate_number") ?> ", "class": "all"},
                {visible: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 3, "class": "w5p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {title: "<?php echo app_lang("has_revisions") ?>", "class": "text-center"},
                {title: "<?php echo app_lang("is_bidding") ?>", "class": "text-center"},
                {visible: showCommentOption, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
                <?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ],
            summation: [{column: 11, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>