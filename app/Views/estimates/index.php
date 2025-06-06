<div id="page-content" class="page-wrapper clearfix grid-button">

    <div class="card clearfix">
        <ul id="estimate-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang('estimates'); ?></h4></li>
            <li><a id="monthly-estimate-button"  role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-estimates"><?php echo app_lang("monthly"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("estimates/yearly/"); ?>" data-bs-target="#yearly-estimates"><?php echo app_lang('yearly'); ?></a></li>
            <div class="tab-title clearfix no-border estimate-page-title">
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("estimates/import_estimates_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_estimates'), array("class" => "btn btn-default", "title" => app_lang('import_estimates'))); ?>
                    <?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_estimate'), array("class" => "btn btn-default", "title" => app_lang('add_estimate'))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-estimates">
                <div class="table-responsive">
                    <table id="monthly-estimate-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-estimates"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadEstimatesTable = function (selector, dateRange) {
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_estimates") == "1" ?>") {
            showCommentOption = true;
        }

        $(selector).appTable({
            source: '<?php echo_uri("estimates/list_data") ?>',
            order: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [
                {name: "status", class: "w150", options: <?php echo view("estimates/estimate_statuses_dropdown"); ?>},
                {name: "is_bidding", class: "w150", options: <?php echo  view("estimates/estimate_bidding_dropdown");?>},
            <?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("estimate_number") ?> ", "class": "all"},
                {visible: false},
                {title: "<?php echo app_lang("client") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 3, "class": "w5p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {title: "<?php echo app_lang("has_revisions") ?>", "class": "text-center"},
                {title: "<?php echo app_lang("is_bidding") ?>", "class": "text-center"},
                {visible: showCommentOption, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
                <?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ],
            printColumns: combineCustomFieldsColumns([0, 2, 4, 5, 6, 7, 8], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 2, 4, 5, 6, 7, 8], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 11, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol, conversionRate: <?php echo $conversion_rate; ?>}]
        });
    };
    $(document).ready(function () {
        loadEstimatesTable("#monthly-estimate-table", "monthly");
    });

</script>