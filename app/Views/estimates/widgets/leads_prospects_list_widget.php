<div class="card bg-white">
    <div class="card-header no-border">
        <i data-feather="users" class="icon-16"></i>&nbsp; <?php echo "Conversão Leads x Prospects"; ?>
    </div>
    <ul id="leads-prospects-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-leads-prospects"><?php echo app_lang("monthly"); ?></a></li>
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#yearly-leads-prospects"><?php echo app_lang('yearly'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="monthly-leads-prospects">
            <div class="table-responsive">
                <table id="leads_prospects_table" class="display" width="100%">
                </table>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="yearly-leads-prospects">
            <div class="table-responsive">
                <table id="leads_prospects_table-yearly" class="display" width="100%">
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

        $("#leads_prospects_table").appTable({
            source: '<?php echo_uri("clients/leads_prospects") ?>',
            serverSide: false,
            order: [[0, "desc"]],            
            dateRangeType: "monthly",
            responsive: false, //hide responsive (+) icon
            filterDropdown: [
                {name: "seller", class: "w150", options: <?php echo $collaborators_dropdown; ?>}
            ],
            columns: [
                {title: '<?php echo app_lang("seller") ?>', "class": titleColumnClass},
                {title: '<?php echo app_lang("new") . ' ' . app_lang("leads") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("new") . ' ' . app_lang("prospects") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("conversion") ?>', "class": "w15p"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });

        $("#leads_prospects_table-yearly").appTable({
            source: '<?php echo_uri("clients/leads_prospects/yearly") ?>',
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
                {title: '<?php echo app_lang("new") . ' ' . app_lang("leads") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("new") . ' ' . app_lang("prospects") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("conversion") ?>', "class": "w15p"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });
    });
</script>