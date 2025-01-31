<div id="page-content" class="page-wrapper clearfix dashboard-view">

    <?php
    if (count($dashboards)) {
        echo view("dashboards/dashboard_header");
    }
    else{
        echo view("dashboards/unique_dashboard_header");
    }
    ?>

    <div class="clearfix row">
        <div class="col-md-12 widget-container">
            <?php
            echo announcements_alert_widget();

            app_hooks()->do_action('app_hook_dashboard_announcement_extension');
            ?>
        </div>
    </div>
    <div class="dashboard_items_container">
        <?php
        if ($widget_columns) {
            echo $widget_columns;
        } else {
            echo view("dashboards/custom_dashboards/no_widgets");
        }

        $dashboard_id = isset($dashboard_id) ? $dashboard_id : 0;
        ?>
    </div>

</div>

<?php echo view("dashboards/helper_js"); ?>

<script>
    $(document).ready(function () {
        //we have to reload the same page when editting title
        $("#dashboard-edit-title-button").click(function () {
            window.dashboardTitleEditMode = true;
        });

        

        var header = $(".dashbaord-header-area");
        var first_row = $(".dashboards-row:first"); // Pegando apenas o primeiro elemento com a classe dashboards-row

        // Coloca o header abaixo do first_row
        header.before(first_row);

        $(".dashboards-row").first().css({
            "background-color": "rgb(226, 231, 242)",
            "padding-top": "17px",
            "border-radius": "5px"
        });
        $(".dashboards-row").first().find(".dashboard-icon-widget").css({
            "background-color": "rgb(238, 241, 249)"
        });
        
        $(".dashboards-row").first().find(".bg-transparent-white").css({
            "background-color": "transparent"
        });



        //update dashboard link
        //$(".dashboard-menu, .dashboard-image").closest("a").attr("href", window.location.href);

        onDashboardDeleteSuccess = function (result, $selector) {
            window.location.href = "<?php echo get_uri("dashboard"); ?>";
        };

        initScrollbar('#project-timeline-container', {
            setHeight: 719
        });

        initScrollbar('#upcoming-event-container', {
            setHeight: 330
        });

        initScrollbar('#client-projects-list', {
            setHeight: 316
        });

<?php if ($dashboard_id && $dashboard_id === get_setting("staff_default_dashboard") && $login_user->user_type === "staff") { ?>
            $(".dashboards-row").each(function () { //each widgets row
                var $rowInstance = $(this),
                        totalColumns = $rowInstance.find(".widget-container").length,
                        invalidWidgetRemoved = false;

                //remove invalid widgets and columns
                $rowInstance.find(".widget-container").each(function () { //each widgets column
                    var invalidWidget = $(this).find(".dashboard-invalid-widget");

                    if (invalidWidget) { //has invalid widget in this column
                        invalidWidget.remove(); //remove invalid widget
                        if ($(this).text() === '') { //if there is nothing else in this column the remove the column
                            $(this).remove();
                            invalidWidgetRemoved = true; //flag an invalid widget removed, to prevent extra operations
                        }
                    }
                });

                if (invalidWidgetRemoved) {
                    var totalNewColumns = $rowInstance.find(".widget-container").length,
                            columnsArray = {1: 12, 2: 6, 3: 4, 4: 3};

                    if (totalColumns !== totalNewColumns) { //any column has been totally removed in this row
                        $rowInstance.find(".widget-container").each(function () {
                            $(this).addClass("col-md-" + columnsArray[totalNewColumns]); //apply the appropriate column class
                        });
                    }
                }
            });
<?php } ?>

    });
</script>