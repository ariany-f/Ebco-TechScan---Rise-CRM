<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>

    <div class="card rounded-bottom">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('projects'); ?></h1>
                <div class="title-button-group clients-project-page-title">
                    <?php
                    if (isset($can_create_projects) && $can_create_projects) {
                        echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_project'), array("class" => "btn btn-default", "data-post-client_id" => $client_id, "title" => app_lang('add_project')));
                    }
                    ?>
                </div>
            </div>
        <?php } else if (isset($page_type) && $page_type === "dashboard") { ?>
            <div class="page-title bg-info text-white clearfix">
                <h1><?php echo app_lang('projects'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('projects'); ?></h4>
                <div class="title-button-group">
                    <?php
                    if (isset($can_create_projects) && $can_create_projects) {
                        echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_project'), array("class" => "btn btn-default", "data-post-client_id" => $client_id, "title" => app_lang('add_project')));
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="table-responsive" id="client-projects-list">
            <table id="project-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>

<?php
if (!isset($project_labels_dropdown)) {
    $project_labels_dropdown = "0";
}
?>


<script type="text/javascript">
    $(document).ready(function () {
        var hideTools = "<?php
if (isset($page_type) && $page_type === 'dashboard') {
    echo 1;
}
?>" || 0;


        var filters = [];

        //don't show filters if hideTools is true 
        if (hideTools) {
            filters = false;
        } else {
            if (<?php echo $project_labels_dropdown; ?>) {
                var filters = [{name: "project_label", class: "w200", options: <?php echo $project_labels_dropdown; ?>}, <?php echo $custom_field_filters; ?>];
            } else {
                //$project_labels_dropdown is empty
                var filters = [<?php echo $custom_field_filters; ?>];
            }
        }

        var optionVisibility = false;
        if ("<?php echo get_setting("client_can_edit_projects"); ?>") {
            optionVisibility = true;
        }


        $("#project-table").appTable({
            source: '<?php echo_uri("projects/projects_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            hideTools: hideTools,
            multiSelect: [
                {
                    name: "status",
                    text: "<?php echo app_lang('status'); ?>",
                    options: [
                        {text: '<?php echo app_lang("new_project") ?>', value: "new_project", isChecked: true},
                        {text: '<?php echo app_lang("open_project") ?>', value: "open_project", isChecked: true},
                        {text: '<?php echo app_lang("completed_project") ?>', value: "completed_project"},
                        {text: '<?php echo app_lang("hold_project") ?>', value: "hold_project"},
                        {text: '<?php echo app_lang("canceled_project") ?>', value: "canceled_project"}
                    ]
                }
            ],
            filterDropdown: filters,
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "w50"},
                {title: '<?php echo app_lang("title") ?>'},
                {targets: [2], visible: false, searchable: false},
                {title: '<?php echo app_lang("price") ?>', "class": "w10p"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("start_date") ?>', "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("deadline") ?>', "class": "w10p", "iDataSort": 6},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p"}
<?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 5, 7, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 5, 7, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>