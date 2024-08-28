<a href="<?php echo get_uri("clients/index/clients_list"); ?>" class="white-link">
    <div class="card  dashboard-icon-widget">
        <div class="card-body">
            <div class="widget-icon bg-primary">
                <i data-feather="briefcase" class="icon"></i>
            </div>
            <div class="widget-details">
                <h1><?php echo $total; ?></h1>
                <span class="bg-transparent-white"><?php echo app_lang("total_clients"); ?></span><br/>
                <small style="color:#adb5bd;">* Não filtrável (Reflete a situação atual da base)</small>
            </div>
        </div>
    </div>
</a>