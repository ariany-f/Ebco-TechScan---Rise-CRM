

            <?php echo $estimate_preview; ?>
            <script
                src="https://code.jquery.com/jquery-3.7.1.min.js"
                integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
                crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

            <script type="text/javascript">
                $(document).ready(function () {

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

                    $.ajax({
                        url: "<?php echo get_uri('estimate/print_estimate/' . $estimate_info->id . '/' . $estimate_info->public_key) ?>",
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {
                                let div = result.print_view;
                                document.body.innerHTML = div; //add estimate's print view to the page
                                let noButtonsDiv = document.body.getElementsByClassName('invoice-preview-container')[0].innerHTML;
                                $('#controls-estimate').addClass('hide');
                                doc.setFontSize(10)
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
                                
                                $("body").css({"overflow": "scroll"});

                            } else {

                            }
                        }
                    });
                });
            </script>