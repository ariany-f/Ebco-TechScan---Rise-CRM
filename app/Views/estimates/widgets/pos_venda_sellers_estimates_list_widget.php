<div class="card bg-white">
    <div class="card-header no-border">
        <i data-feather="tablet" class="icon-16"></i>&nbsp; <?php echo "Propostas Emitidas x Fechadas Pós-Vendas"; ?>
    </div>
    <ul id="estimate-sellers-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-estimates-pos-venda-sellers"><?php echo app_lang("monthly"); ?></a></li>
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#yearly-estimates-pos-venda-sellers"><?php echo app_lang('yearly'); ?></a></li>
    </ul> 
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="monthly-estimates-pos-venda-sellers">
            <div class="table-responsive">
                <table id="aux-table" class="display" width="100%">            
                </table>
            </div> 
        </div>
        <div role="tabpanel" class="tab-pane fade" id="yearly-estimates-pos-venda-sellers">
            <div class="table-responsive">
            <table id="aux-table-yearly" class="display" width="100%">            
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

        $("#aux-table-yearly").appTable({
            source: '<?php echo_uri("estimates/get_sellers_estimates/yearly/true") ?>',
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
                {title: '<?php echo app_lang("estimates_emmited") ?>', "class": "w5p text-center"},
                {title: '<?php echo app_lang("estimates_approved") ?>', "class": "w5p text-center"},
                {title: '<?php echo '% ' . app_lang("conversion") ?>', "class": idColumnClass},
                {title: '<?php echo app_lang("total") ?>', "class": "w15p"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });

        $("#aux-table").appTable({
            source: '<?php echo_uri("estimates/get_sellers_estimates/monthly/true") ?>',
            serverSide: false,
            order: [[0, "desc"]],            
            dateRangeType: "monthly",
            responsive: false, //hide responsive (+) icon
            filterDropdown: [
                {name: "seller", class: "w150", options: <?php echo $collaborators_dropdown; ?>}
            ],
            columns: [
                {title: '<?php echo app_lang("seller") ?>', "class": titleColumnClass},
                {title: '<?php echo app_lang("estimates_emmited") ?>', "class": "w5p text-center"},
                {title: '<?php echo app_lang("estimates_approved") ?>', "class": "w5p text-center"},
                {title: '<?php echo '% ' . app_lang("conversion") ?>', "class": idColumnClass},
                {title: '<?php echo app_lang("total") ?>', "class": "w15p"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });
    });
</script>