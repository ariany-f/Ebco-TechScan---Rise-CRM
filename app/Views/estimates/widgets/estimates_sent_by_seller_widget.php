<div class="card bg-white">
    <div class="card-header no-border">
        <span class="badge bg-primary">Enviadas</span>&nbsp; <?php echo "Suas Propostas Enviadas sem Resposta do Cliente"; ?>
    </div>
    <ul id="estimate-sellers-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#sent_estimates"><?php echo app_lang("general"); ?></a></li>
    </ul> 
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="sent_estimates">
            <div class="table-responsive">
                <table id="sent" class="display" width="100%">
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

        $("#sent").appTable({
            source: '<?php echo_uri("estimates/list_data") ?>',
            serverSide: false,
            order: [[0, "desc"]],
            filterParams: {
                status: "sent",
                is_bidding: 0,
                "seller_ids[]": <?= $login_user->id  ?>
            },
            responsive: false, //hide responsive (+) icon
            columns: [
                {title: "<?php echo app_lang("estimate_number") ?> ", "class": "all"},
                {visible: false},
                {title: "<?php echo app_lang("client") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 3, "class": "w5p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-center w10p"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false}
                <?php echo $custom_field_headers; ?>
            ],
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");
            }
        });
    });
</script>