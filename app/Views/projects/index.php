<div id="page-content" class="page-wrapper clearfix">
    <div class="card grid-button">
        <div class="page-title clearfix projects-page">
            <h1><?php echo app_lang('projects'); ?></h1>
            <div class="title-button-group">
                <?php
                if ($can_create_projects) {
                    if ($can_edit_projects) {
                        echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "project"));
                    }

                    echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_project'), array("class" => "btn btn-default", "title" => app_lang('add_project')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="project-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo ($can_edit_projects || $can_delete_projects); ?>") {
            optionVisibility = true;
        }

        var selectNewStatus = true, selectOpenStatus = true, selectCompletedStatus = false, selectHoldStatus = false;
<?php if (isset($status) && $status == "completed_project") { ?>
            selectOpenStatus = false;
            selectCompletedStatus = true;
            selectNewStatus = false;
            selectHoldStatus = false;
<?php } else if (isset($status) && $status == "open_project") { ?>
            selectOpenStatus = true;
            selectCompletedStatus = false;
            selectNewStatus = false;
            selectHoldStatus = false;
<?php } else if (isset($status) && $status == "hold_project") { ?>
            selectOpenStatus = false;
            selectCompletedStatus = false;
            selectNewStatus = false;
            selectHoldStatus = true;
<?php } ?>

        $("#project-table").appTable({
            source: '<?php echo_uri("projects/list_data") ?>',
            multiSelect: [
                {
                    name: "status",
                    text: "<?php echo app_lang('status'); ?>",
                    options: [
                        {text: '<?php echo app_lang("new_project") ?>', value: "new_project", isChecked: selectNewStatus},
                        {text: '<?php echo app_lang("open_project") ?>', value: "open_project", isChecked: selectOpenStatus},
                        {text: '<?php echo app_lang("completed_project") ?>', value: "completed_project", isChecked: selectCompletedStatus},
                        {text: '<?php echo app_lang("hold_project") ?>', value: "hold_project", isChecked: selectHoldStatus},
                        {text: '<?php echo app_lang("canceled_project") ?>', value: "canceled_project"}
                    ]
                }
            ],
            filterDropdown: [{name: "project_label", class: "w200", options: <?php echo $project_labels_dropdown; ?>}, <?php echo $custom_field_filters; ?>],
            singleDatepicker: [{name: "deadline", defaultText: "<?php echo app_lang('deadline') ?>",
                    options: [
                        {value: "expired", text: "<?php echo app_lang('expired') ?>"},
                        {value: moment().format("YYYY-MM-DD"), text: "<?php echo app_lang('today') ?>"},
                        {value: moment().add(1, 'days').format("YYYY-MM-DD"), text: "<?php echo app_lang('tomorrow') ?>"},
                        {value: moment().add(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 7); ?>"},
                        {value: moment().add(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 15); ?>"}
                    ]}],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "all w50"},
                {title: '<?php echo app_lang("title") ?>', "class": "all"},
                {title: '<?php echo app_lang("client") ?>', "class": "w10p"},
                {visible: optionVisibility, title: '<?php echo app_lang("price") ?>', "class": "w10p"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("start_date") ?>', "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("deadline") ?>', "class": "w10p", "iDataSort": 6},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p"}
                <?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>