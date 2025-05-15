<div class="card bg-white">
    <div class="card-header no-border">
        <span class="badge bg-success">Rascunhos</span>&nbsp; <?php echo "Suas Propostas Aguardando Envio"; ?>
    </div>
    <ul id="estimate-sellers-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#principal"><?php echo app_lang("general"); ?></a></li>
    </ul> 
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="principal">
            <div class="table-responsive">
                <table id="draft" class="display" width="100%">
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

        $("#draft").appTable({
            source: '<?php echo_uri("estimates/list_data") ?>',
            serverSide: false,
            order: [[0, "desc"]],
            filterParams: {
                status: "draft",
                is_bidding: 0,
                "seller_ids[]": <?= $login_user->id ?>
            },
            responsive: false, //hide responsive (+) icon
            columns: [
                {title: "<?php echo app_lang("estimate_number") ?> ", "class": "all"},
                {visible: false},
                {title: "<?php echo app_lang("client") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 3, "class": "w5p"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false}
                <?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ],
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });
    });
</script>
