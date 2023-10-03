<div class="no-border clearfix mb0">
    <div class="mt15">
        <div class="row">
            <div class="col-md-4">
                <div id="estimate_template-list-box" class="card">
                    <div class="page-title clearfix">
                        <h4> <?php echo app_lang('estimate_templates'); ?></h4>
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("estimate_templates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_estimate_template'), array("class" => "btn btn-default", "title" => app_lang('add_estimate_template'))); ?>
                        </div>
                    </div>
                    <div class="table-responsiv">
                        <table id="estimate_template-table" class="display clickable no-thead b-b-only" cellspacing="0" width="100%">            
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div id="estimate_template-details-section"> 
                    <div id="empty-estimate_template" class="text-center p15 box card " style="min-height: 150px;">
                        <div class="box-content" style="vertical-align: middle; height: 100%"> 
                            <div><?php echo app_lang("select_a_template"); ?></div>
                            <span data-feather="code" width="6rem" height="6rem" style="color:rgba(128, 128, 128, 0.1)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function () {
        $("#estimate_template-table").appTable({
            source: '<?php echo_uri("estimate_templates/list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("name"); ?>'},
                {title: '', class: 'text-center option w125'}
            ],
            hideTools: true,
            onInitComplete: function () {
                var $estimate_template_list = $("#estimate_template-list-box"),
                        $empty_estimate_template = $("#empty-estimate_template");
                if ($empty_estimate_template.length && $estimate_template_list.length) {
                    $empty_estimate_template.height($estimate_template_list.height() - 30);
                }
            },
            displayLength: 1000
        });

        /*load a message details*/
        $("body").on("click", "tr", function () {
            //don't load this message if already has selected.
            if (!$(this).hasClass("active")) {
                var estimate_template_id = $(this).find(".estimate_template-row").attr("data-id");
                console.log(estimate_template_id);
                if (estimate_template_id) {
                    appLoader.show();
                    $("tr.active").removeClass("active");
                    $(this).addClass("active");
                    $.ajax({
                        url: "<?php echo get_uri("estimate_templates/form"); ?>/" + estimate_template_id,
                        success: function (result) {
                            appLoader.hide();
                            $("#estimate_template-details-section").html(result);
                        }
                    });
                }
            }
        });
    });
</script>