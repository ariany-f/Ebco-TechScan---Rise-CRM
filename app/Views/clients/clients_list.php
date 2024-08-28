<div class="card">
    <div class="table-responsive">
        <table id="client-table" class="display" cellspacing="0" width="100%">            
        </table>
    </div>
</div>
<?php
//prepare status dropdown list
//select the non completed tasks for team members by default
//show all tasks for client by default.
$statuses = array();
foreach ($client_statuses as $status) {
    $is_selected = false;
    if ($status->title != "Ativo") {
        $is_selected = true;
    }
    $statuses[] = array("text" => ($status->title), "value" => $status->status_id, "isChecked" => $is_selected);
}
?>
<script type="text/javascript">
    loadClientsTable = function (selector) {
    var showInvoiceInfo = true;
    if (!"<?php echo $show_invoice_info; ?>") {
    showInvoiceInfo = false;
    }

    var showOptions = true;
    if (!"<?php echo $can_edit_clients; ?>") {
    showOptions = false;
    }

    var quick_filters_dropdown = <?php echo view("clients/quick_filters_dropdown"); ?>;
    if (window.selectedClientQuickFilter){
    var filterIndex = quick_filters_dropdown.findIndex(x => x.id === window.selectedClientQuickFilter);
    if ([filterIndex] > - 1){
    //match found
    quick_filters_dropdown[filterIndex].isSelected = true;
    }
    }

    $(selector).appTable({
    source: '<?php echo_uri("clients/list_data") ?>',
            serverSide: true,
            filterDropdown: [
            {name: "group_id", class: "w200", options: <?php echo $groups_dropdown; ?>},
            {name: "setor", class: "w200", options: <?php echo $setor_dropdown; ?>},
            <?php if ($login_user->is_admin || get_array_value($login_user->permissions, "client") === "all") { ?>
                            {name: "created_by", class: "w200", options: <?php echo $team_members_dropdown; ?>},
            <?php } ?>
            {name: "quick_filter", class: "w200", options: quick_filters_dropdown},
            <?php echo $custom_field_filters; ?>
            ],
            multiSelect: [
            {
                name: "status_id",
                        text: "<?php echo app_lang('status'); ?>",
                        options: <?php echo json_encode($statuses); ?>,
                        saveSelection: true
                }
            ],
            columns: [
            {title: "<?php echo app_lang("id") ?>", "class": "text-center w50 all", order_by: "id"},
            {title: "<?php echo app_lang("company_name") ?>", "class": "all", order_by: "company_name"},
            {title: "<?php echo app_lang("setor") ?>", "class": "all", order_by: "setor"},
            {title: "<?php echo app_lang("state") ?>", order_by: "state"},
            {title: "<?php echo app_lang("primary_contact") ?>", order_by: "primary_contact"},
            {title: "<?php echo app_lang("limit_date_for_nota_fiscal") ?>", order_by: "limit_date_for_nota_fiscal"},
            {title: "<?php echo app_lang("estimates_sent") ?>"},
            {title: "<?php echo app_lang("estimates_approved") ?>"},
            {title: "<?php echo app_lang("projects") ?>"},
            {visible: showInvoiceInfo, searchable: showInvoiceInfo, title: "<?php echo app_lang("total_invoiced") ?>"},
            {visible: showInvoiceInfo, searchable: showInvoiceInfo, title: "<?php echo app_lang("payment_received") ?>"},
            {visible: showInvoiceInfo, searchable: showInvoiceInfo, title: "<?php echo app_lang("due") ?>"}
            <?php echo $custom_field_headers; ?>,
            {title: "<?php echo app_lang("status") ?>"},
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", visible: showOptions}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>')
    });
    };
    $(document).ready(function () {
    loadClientsTable("#client-table");
    });
</script>