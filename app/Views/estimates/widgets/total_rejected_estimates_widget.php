<a href="<?php echo get_uri("estimates/index"); ?>" class="white-link">
    <div class="card  dashboard-icon-widget">
        <div class="card-body">
            <div class="widget-icon bg-danger">
                <img width="25px" src="<?php echo base_url('assets/images/icons/receipt-solid.svg'); ?>">
            </div>
            <div class="widget-details">
                <h1><?php echo $total; ?></h1>
                <span class="bg-transparent-white"><?php echo $login_user->is_admin ? app_lang("total_rejected_estimates") : 'Total Propostas Emitidas por Você Rejeitadas no Período'; ?></span>
            </div>
        </div>
    </div>
</a>