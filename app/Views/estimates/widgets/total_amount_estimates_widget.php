<a href="<?php echo get_uri("estimates/index"); ?>" class="white-link">
    <div class="card  dashboard-icon-widget">
        <div class="card-body">
            <div class="widget-icon bg-green">
                <i data-feather="dollar-sign" class="icon"></i>
            </div>
            <div class="widget-details">
                <h1><?php echo to_currency($total); ?></h1>
                <span class="bg-transparent-white"><?php echo app_lang("total_amount_estimates"); ?></span>
            </div>
        </div>
    </div>
</a>