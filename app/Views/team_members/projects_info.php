<div class="card rounded-0">
    <div class="tab-title clearfix">
        <h4><?php echo app_lang('projects'); ?></h4>
    </div>
    <div class="table-responsive">
        <table id="project-table" class="display" cellspacing="0" width="100%">
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#project-table").appTable({
            source: '<?php echo_uri("projects/projects_list_data_of_team_member/" . $user_id) ?>',
            radioButtons: [{text: '<?php echo app_lang("open_project") ?>', name: "status", value: "open_project", isChecked: true}, {text: '<?php echo app_lang("completed_project") ?>', name: "status", value: "completed_project", isChecked: false}, {text: '<?php echo app_lang("new_project") ?>', name: "status", value: "new_project", isChecked: false}, {text: '<?php echo app_lang("hold_project") ?>', name: "status", value: "hold_project", isChecked: false}, {text: '<?php echo app_lang("canceled_project") ?>', name: "status", value: "canceled_project", isChecked: false}],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "w50"},
                {title: '<?php echo app_lang("title") ?>'},
                {title: '<?php echo app_lang("client") ?>', "class": "w10p"},
                {visible: true, title: '<?php echo app_lang("price") ?>', "class": "w10p"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("start_date") ?>', "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("deadline") ?>', "class": "w10p", "iDataSort": 6},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p"}
<?php echo $custom_field_headers; ?>
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>