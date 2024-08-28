<div id="page-content" class="clearfix">
    <?php
        load_css(array(
            "assets/css/invoice.css",
            "assets/css/estimate.css",
        ));
    ?>
   
    <div class="invoice-preview print-invoice">
        <?php if((!isset($buttons)) || $buttons): ?>
            <div class="invoice-preview-container bg-white mt15">
                <div class="row">
                    <div class="col-md-12 position-relative">
                        <div class="ribbon"><?php echo $estimate_status_label; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $estimate_preview; ?>
    </div>
    
</div>

<script type="text/javascript">
    $(document).ready(function () {
       // $("html, body").addClass("dt-print-view");
    });
</script>