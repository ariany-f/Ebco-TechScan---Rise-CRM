<a href="<?php echo get_uri("estimates/index"); ?>" class="white-link">
    <div class="card  dashboard-icon-widget">
        <div class="card-body">
            <div class="widget-icon bg-primary">
                <img width="35px" src="<?php echo base_url('assets/images/icons/handshake-regular.svg'); ?>">
            </div>
            <div class="widget-details">
                <h1><?php echo $total; ?></h1>
                <span class="bg-transparent-white"><?php echo $login_user->is_admin ? app_lang("total_approved_estimates") : 'Total Propostas Emitidas por Você Aprovadas no Período'; ?></span>
            </div>
        </div>
    </div>
</a>