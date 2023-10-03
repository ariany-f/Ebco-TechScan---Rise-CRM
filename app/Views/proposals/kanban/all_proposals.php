<div id="page-content" class="page-wrapper pb0 clearfix">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("proposals"); ?></h4></li>

        <?php echo view("proposals/tabs", array("active_tab" => "proposals_kanban")); ?>      

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <!-- Nenhum botão aqui -->
            </div>
        </div>
    </ul>
    <div class="bg-white kanban-filters-container">
        <div class="row">
            <div class="col-md-1 col-xs-2">
                <button class="btn btn-default" id="reload-kanban-button"><i data-feather="refresh-cw" class="icon-16"></i></button>
            </div>
            <div id="kanban-filters" class="col-md-11 col-xs-10"></div>
        </div>
    </div>

    <div id="load-kanban"></div>
</div>

<script>
    $(document).ready(function () {
        window.scrollToKanbanContent = true;
    });
</script>

<?php echo view("proposals/kanban/all_proposals_kanban_helper_js"); ?>