<script type="text/javascript">

    $(document).ready(function () {

        var scrollLeft = 0;
        $("#kanban-filters").appFilters({
            source: '<?php echo_uri("proposals/all_proposals_kanban_data") ?>',
            targetSelector: '#load-kanban',
            reloadSelector: "#reload-kanban-button",
            search: {name: "search"},
            filterDropdown: [
                <?php if (get_array_value($login_user->permissions, "proposal") !== "own") { ?>
                                    , {name: "owner_id", class: "w200", options: <?php echo json_encode($owners_dropdown); ?>}
                <?php } ?>
                , <?php echo $custom_field_filters; ?>
            ],
            beforeRelaodCallback: function () {
                scrollLeft = $("#kanban-wrapper").scrollLeft();
            },
            afterRelaodCallback: function () {
                setTimeout(function () {
                    $("#kanban-wrapper").animate({scrollLeft: scrollLeft}, 'slow');
                }, 500);
            }
        });

    });

</script>