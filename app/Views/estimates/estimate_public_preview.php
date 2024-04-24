<!DOCTYPE html>
<html lang="en">
    <head>
        <?php echo view('includes/head'); ?>
    </head>
    <body>

        <style>
            .card {
                transition: all 0s !important;
            }

            .mt4{
                margin-top: 4px;
            }

            .timeline-images {
                display: block;
                page-break-before: always;
                clear: both;
            }

            .timeline-images img {
                min-height: calc(1.5 * 841.89px);
                height: calc(1.5 * 841.89px);
                width: 100%;
            }
        </style>

        <div id="estimate-preview-scrollbar">

            <div id="page-content" class="page-wrapper clearfix">
                <?php
                load_css(array(
                    "assets/css/invoice.css",
                ));

                load_js(array(
                    "assets/js/signature/signature_pad.min.js",
                ));
                
                ?>

                <div class="invoice-preview estimate-preview">
                    <div id="controls-estimate" class = "card  p15 no-border">
                        <div class="clearfix">
                            <input type="hidden" id="estimate_date" value="<?= format_to_date($estimate_info->estimate_date, false) ?>">
                            <?php if ($estimate_info->status === "accepted" || $estimate_info->status === "declined" || $estimate_info->status === "rejected") { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="float-end mt10 mr15">
                                    <?php if ($estimate_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("estimate_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("estimate_rejected"); ?>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="strong float-end mt4">
                                    <?php echo ajax_anchor(get_uri("estimate/update_estimate_status/$estimate_info->id/$estimate_info->public_key/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject_estimate'), array("class" => "btn btn-danger mr10", "title" => app_lang('reject_estimate'), "data-reload-on-success" => "1")); ?>
                                    <?php echo modal_anchor(get_uri("estimate/accept_estimate_modal_form/$estimate_info->id/$estimate_info->public_key"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept_estimate'), array("class" => "btn btn-success mr5", "title" => app_lang('accept_estimate'))); ?>
                                    <?php echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('download_pdf'), array('title' => app_lang('download_pdf'), 'id' => 'print-estimate-btn', "class" => "btn btn-primary mr5")); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="invoice-preview-container estimate-preview-container bg-white">
                        <?php
                        echo $estimate_preview;
                        ?>
                    </div>

                </div>
            </div>

            <?php echo view('modal/index'); ?>

        </div>
       
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

        <script>
            $(document).ready(function () {
                initScrollbar('#estimate-preview-scrollbar', {
                    setHeight: $(window).height()
                });

                $("#custom-theme-color").remove();

                window.jsPDF = window.jspdf.jsPDF;
                var doc = new jsPDF();

                const addFooters = (doc) => {
                    const pageCount = doc.internal.getNumberOfPages()

                    doc.setFont('helvetica', 'italic')
                    doc.setFontSize(8)
                    for (var i = 1; i <= pageCount; i++) {
                        doc.setPage(i)
                        doc.text('Data de Emissão ' + $("#estimate_date").val() + ' Página ' + String(i) + ' de ' + String(pageCount), doc.internal.pageSize.width / 2, 287, {
                        align: 'center'
                    })
                    }
                }

                //print estimate
                $("#print-estimate-btn").click(function () {
                    appLoader.show();

                    $.ajax({
                        url: "<?php echo get_uri('estimate/print_estimate/' . $estimate_info->id . '/' . $estimate_info->public_key) ?>",
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {
                                let div = result.print_view;
                                document.body.innerHTML = div; //add estimate's print view to the page
                                let noButtonsDiv = document.body.getElementsByClassName('invoice-preview-container')[0].innerHTML;
                               
                               doc.html(noButtonsDiv, {
                                    compress: true,
                                    putOnlyUsedFonts: true,
                                    orientation: 'p',
                                    unit: 'mm',
                                    format: 'a4',
                                    margin: [10, 0, 20 ,0], // the default is [0, 0, 0, 0]
                                    callback: function(doc) {
                                        // Save the PDF
                                        addFooters(doc)
                                        doc.save('Proposta ' + <?php echo $estimate_info->id ?> + '.pdf');
                                    },
                                    x: 0,
                                    y: 0,
                                    width: 210, //target width in the PDF document
                                    windowWidth: 810 //window width in CSS pixels
                                });

                            } else {
                                appAlert.error(result.message);
                            }

                            appLoader.hide();
                        }
                    });
                });

            });
        </script>
    </body>
</html>










