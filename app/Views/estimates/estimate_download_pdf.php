

            <style>
                html, body, div, span, applet, object, iframe,
            h1, h2, h3, h4, h5, h6, p, blockquote, pre,
            a, abbr, acronym, address, big, cite, code,
            del, dfn, em, img, ins, kbd, q, s, samp,
            small, strike, strong, sub, sup, tt, var,
            b, u, i, center,
            dl, dt, dd, ol, ul, li,
            fieldset, form, label, legend,
            table, caption, tbody, tfoot, thead, tr, th, td,
            article, aside, canvas, details, embed, 
            figure, figcaption, footer, header, hgroup, 
            menu, nav, output, ruby, section, summary,
            time, mark, audio, video {
                margin: 0;
                padding: 0;
                border: 0;
                font-size: 100%;
                font: inherit;
                vertical-align: baseline;
            }
            /* HTML5 display-role reset for older browsers */
            article, aside, details, figcaption, figure, 
            footer, header, hgroup, menu, nav, section {
                display: block;
            }
            body {
                line-height: 1;
            }
            ol, ul {
                list-style: none;
            }
            blockquote, q {
                quotes: none;
            }
            blockquote:before, blockquote:after,
            q:before, q:after {
                content: '';
                content: none;
            }
            table {
                border-collapse: collapse;
                border-spacing: 0;
            }

            /** Personalizado */

            @import url('https://fonts.googleapis.com/css?family=Helvetica:300,300i,400,500,600,700,700i,800');

            :root {
                --primary-color: #1f497d;
                --secondary-table-color: #cacfe1;
            }
            body {
                font-family: "Helvetica", sans-serif;
                font-weight:400;
                margin: 0 auto;
                color: var(--primary-color);
                font-size: 12px;
                line-height: 16px;
            }
            body b {
                font-weight: 700;
            }
            body table {
                margin: 0 auto;
                word-spacing: 2px;
            }
            body table td{
                box-sizing: border-box;
                max-width: 100%;
                margin: 0 auto;
            }
            table {
                font-family: "Helvetica", sans-serif;
                margin-top: 0;
            }
            table tr {
                text-align: center;
            }
            h1, h2, h3, h4, h5, h6 {
                font-weight:700;
            }
            table tr:not(.header) td {
                padding-top: .5rem;
                padding-bottom: .5rem;
            }
            table .title td{
                padding-top: 1rem!important;
                padding-bottom: 1rem!important;
            }
            table .title {
                font-size: 16px;
                line-height: 18px;
            }
            table .subtitle {
                font-size: 14px;
                line-height: 16px;
                text-align: left;
            }
            table .content {
                text-align: justify;
                font-size: 14px;
                line-height: 16px;
            }
            table .content td{
                padding-top: 0!important;
                color: rgb(70, 70, 70);
            }
            table .assinatura {
                page-break-inside: avoid!important;
                page-break-after: always;
            }
            table .assinatura td {
                padding-top: 1rem!important;
                clear: both;
            }
            table .assinatura img{
                width: 50%;
            }            
            table .image-centro td {
                padding-top: 1rem!important;
                clear: both;
            }
            table .image-centro img{
                width: 50%;
            }
            table img {
                margin: 0 auto;
            }
            table .bordered-table td {
                border: 1px outset var(--primary-color);
                border-radius: 5px;
                display: grid;
                text-align: left;
                width: 100%;
                padding: 1rem;
                margin-top: 1rem;
                margin-bottom: 1rem;
            }
            table.no-gut {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            table.items, table.items td{
                min-width: 100%;
                border: .8px solid #ededed;
                height: 2rem;
                padding: 0 !important;
                vertical-align: middle;
                line-height: 14px;
                font-size: 13px;
            }
            table.items .table-header {
                line-height: 20px!important;
                height: 2.2rem;
                background-color: var(--primary-color); 
                color: #fff;
            }
            table.items .table-header td{
                border: .8px solid #395577!important;
            }
            table.items .table-sum {
                background-color: var(--secondary-table-color); 
                line-height: 20px;
                font-size: 14px;
                margin-bottom: 20px;
            }
            table.items .table-content {
                line-height: 20px;
                font-size: 14px;
            }
            .deslocamentos {
                width: 40%;
            }
            .deslocamentos tr {
                line-height: 0.6rem!important;
                height: 0.6rem!important;
                font-size: 12px;
            }
            .deslocamentos tr td {
                border: .8px solid #395577;
                padding-top: .4rem!important;
                padding-bottom: .4rem!important;
            }
            .page-break {
                page-break-inside: avoid;
                page-break-after: always;
            }
            
            .page-break>td {
                padding: 0!important;
            }

            .p-0 {
                padding: 0!important;
            }
            </style>

            <?php echo $estimate_preview; ?>
            <?php if($show_js != 0) :?>
            <script
                src="https://code.jquery.com/jquery-3.7.1.min.js"
                integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
                crossorigin="anonymous"></script>
                <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.0/purify.min.js "></script> -->
                <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script> -->
                <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->
                <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" integrity="sha512-2/YdOMV+YNpanLCF5MdQwaoFRVbTmrJ4u4EpqS/USXAQNUDgI5uwYi6J98WVtJKcfe1AbgerygzDFToxAlOGEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script type="text/javascript">
                $(document).ready(function () {

                    $.ajax({
                        url: "<?php echo get_uri('estimate/print_estimate/' . $estimate_info->id . '/' . $estimate_info->public_key) ?>",
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {

                                let div = result.print_view;

                                document.body.innerHTML = div; //add estimate's print view to the page
                            
                                const noButtonsDiv = document.querySelector('#my-table');
                               
                                $('#controls-estimate').addClass('hide');
                                //window.devicePixelRatio = 5;
                                const options = {
                                    margin: [50, 15, 38, 25],
                                    filename: 'Proposta <?php echo $estimate_info->id ?>.pdf',
                                    pagebreak: { mode: ['avoid-all', 'css', 'legacy'], avoid: ['img', 'tr', 'td', 'p']},
                                    html2canvas: {scale: 1.8, letterRendering: true},
                                    jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true, format: 'letter', compressPDF: true, pagesplit: true, autoPaging: true}
                                };
                                //.save() Download PDF
                                html2pdf().set(options).from(noButtonsDiv).toPdf().get('pdf').then((pdf) => {
                                   
                                    
                                    const e = pdf.internal.collections.addImage_images;
                                    console.log(e)
                                    for (let i in e) {
                                        e[i].height <= 20 ? pdf.deletePage(+i + 1) : null;
                                    }
                                    
                                    // handle your result here...
                                    var totalPages = pdf.internal.getNumberOfPages();


                                    for (let i = 1; i <= totalPages; i++) {
                                        // set footer to every page
                                        pdf.setPage(i);
                                        // set footer font
                                        //pdf.setFont('helvetica', 'normal')
                                        pdf.setFontSize(10);
                                        pdf.setTextColor(0, 0, 0);
                                        var img = new Image()
                                        img.src = "<?php echo get_uri('assets/images/header.png') ?>"
                                        pdf.addImage(img, 'png', -20, -115, 850, 160)
                                      
                                       // pdf.text(pdf.internal.pageSize.getWidth() - 80, 20, 'Página ' + String(i) + ' de ' + String(totalPages) + '', 'left');
                                        
                                        pdf.text(15, pdf.internal.pageSize.getHeight() - 15, 'Data de Emissão ' + $("#estimate_date").val(), 'left');
                                        pdf.text(pdf.internal.pageSize.getWidth() - 80, pdf.internal.pageSize.getHeight() - 15, 'Página ' + String(i) + ' de ' + String(totalPages) + '', 'left');
                                    }
                                }).outputPdf('bloburl').then((result) => {
                                    window.open(result, '_blank');
                                });
                                
                                $("body").css({"overflow": "scroll"});

                            } else {

                            }
                        }
                    });
                });
            </script>
<?php endif;?>