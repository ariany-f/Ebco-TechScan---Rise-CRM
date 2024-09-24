<div class="card bg-white">
    <div class="card-header no-border">
        <i data-feather="user-check" class="icon-16"></i>&nbsp; <?php echo "Novos Clientes"; ?>
    </div>
    <ul id="new-clients-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-new-clients"><?php echo app_lang("monthly"); ?></a></li>
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#yearly-new-clients"><?php echo app_lang('yearly'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="monthly-new-clients">
            <div class="table-responsive">
                <table id="new_clients_table" class="display" width="100%">
                </table>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="yearly-new-clients">
            <div class="table-responsive">
                <table id="new_clients_table-yearly" class="display" width="100%">
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        var showOption = true,
                idColumnClass = "w10p text-center",
                titleColumnClass = "w20p  text-left";

        if (isMobile()) {
            showOption = false;
            idColumnClass = "w15p text-center";
            titleColumnClass = "w25p text-left";
        }

        $("#new_clients_table-yearly").appTable({
            source: '<?php echo_uri("clients/new_clients/yearly") ?>',
            serverSide: false,
            order: [[0, "desc"]],
            dateRangeType: "yearly",
            responsive: false, //hide responsive (+) icon
            filterDropdown: [
                {name: "seller", class: "w150", options: <?php echo $collaborators_dropdown; ?>}
            ],
            columns: [
                {title: '<?php echo app_lang("month") ?>', "class": titleColumnClass},
                {title: '<?php echo app_lang("seller") ?>', "class": titleColumnClass},
                {title: '<?php echo app_lang("new") . ' ' . app_lang("clients") ?>', "class": "w15p text-right"},
                {title: '<?php echo app_lang("visits") ?>', "class": "w15p text-right"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            },
            summation: [{column: 2, dataType: 'number'}, {column: 3,  dataType: 'number'}]
        });

        $("#new_clients_table").appTable({
            source: '<?php echo_uri("clients/new_clients") ?>',
            serverSide: false,
            order: [[0, "desc"]],            
            dateRangeType: "monthly",
            responsive: false, //hide responsive (+) icon
            filterDropdown: [
                {name: "seller", class: "w150", options: <?php echo $collaborators_dropdown; ?>}
            ],
            columns: [
                {title: '<?php echo app_lang("seller") ?>', "class": titleColumnClass},
                {title: '<?php echo app_lang("new") . ' ' . app_lang("clients") ?>', "class": "w15p text-right"},
                {title: '<?php echo app_lang("visits") ?>', "class": "w15p text-right"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            },
            summation: [{column: 1, dataType: 'number'}, {column: 2,  dataType: 'number'}]
        });
    });
</script>