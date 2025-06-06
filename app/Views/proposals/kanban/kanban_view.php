<div id="kanban-wrapper">
    <?php
    $columns_data = array();

    foreach ($proposals as $proposal) {

        $exising_items = get_array_value($columns_data, $proposal->proposal_status_id);
        if (!$exising_items) {
            $exising_items = "";
        }

        $owner = "";
        if ($proposal->owner_id) {
            $owner = "<br /><small>" . app_lang("owner") . ": " . get_team_member_profile_link($proposal->owner_id, $proposal->owner_name) . "</small>";
        }

        $proposals_total_counts = "<small class='float-end'>";

        if (!$owner) {
            $proposals_total_counts = "<br /><small class='float-end'>";
        }

        //total contacts
        if ($proposal->total_contacts_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("contacts") . "'>" . $proposal->total_contacts_count . " <i data-feather='user' class='icon-16'></i></span> ";
        }

        //total events
        if ($proposal->total_events_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("events") . "'>" . $proposal->total_events_count . " <i data-feather='calendar' class='icon-16'></i></span> ";
        }

        //total notes
        if ($proposal->total_notes_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("notes") . "'>" . $proposal->total_notes_count . " <i data-feather='book' class='icon-16'></i></span> ";
        }

        //total estimates
        if ($proposal->total_estimates_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("estimates") . "'>" . $proposal->total_estimates_count . " <i data-feather='file' class='icon-16'></i></span> ";
        }

        //total estimate requests
        if ($proposal->total_estimate_requests_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("estimate_requests") . "'>" . $proposal->total_estimate_requests_count . " <i data-feather='file' class='icon-16'></i></span> ";
        }

        //total files
        if ($proposal->total_files_count) {
            $proposals_total_counts .= "<span class='mr5' title='" . app_lang("files") . "'>" . $proposal->total_files_count . " <i data-feather='file-text' class='icon-16'></i></span> ";
        }

        $proposals_total_counts .= "</small>";

        $open_in_new_tab = anchor(get_uri("proposals/view/" . $proposal->id), "<i data-feather='external-link' class='icon-16'></i>", array("target" => "_blank", "class" => "invisible float-end text-off", "title" => app_lang("details")));


        //custom fields to show in kanban
        $kanban_custom_fields_data = "";
        $kanban_custom_fields = get_custom_variables_data("proposals", $proposal->id, $login_user->is_admin);
        if ($kanban_custom_fields) {
            foreach ($kanban_custom_fields as $kanban_custom_field) {
                $kanban_custom_fields_data .= "<br /><small>" . get_array_value($kanban_custom_field, "custom_field_title") . ": " . view("custom_fields/output_" . get_array_value($kanban_custom_field, "custom_field_type"), array("value" => get_array_value($kanban_custom_field, "value"))) . "</small>";
            }
        }

        $item = $exising_items . "<span class='proposal-kanban-item kanban-item' data-id='$proposal->id' data-sort='$proposal->new_sort' data-post-id='$proposal->id'>
                    <div><span class='avatar'><img src='" . get_avatar($proposal->primary_contact_avatar) . "'></span>" . anchor(get_uri("proposals/view/" . $proposal->id), $proposal->company_name) . $open_in_new_tab . "</div>" .
                $source .
                $owner .
                $kanban_custom_fields_data .
                $proposals_total_counts .
                "</span>";

        $columns_data[$proposal->proposal_status_id] = $item;
    }
    ?>

    <ul id="kanban-container" class="kanban-container clearfix">

        <?php foreach ($columns as $column) { ?>
            <li class="kanban-col" >
                <div class="kanban-col-title" style="border-bottom: 3px solid <?php echo $column->color ? $column->color : "#2e4053"; ?>;"> <?php echo $column->title; ?> </div>

                <div class="kanban-input general-form hide">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('add_a_proposal')
                    ));
                    ?>
                </div>

                <div  id="kanban-item-list-<?php echo $column->id; ?>" class="kanban-item-list" data-proposal_status_id="<?php echo $column->id; ?>">
                    <?php echo get_array_value($columns_data, $column->id); ?>
                </div>
            </li>
        <?php } ?>

    </ul>
</div>

<img id="move-icon" class="hide" src="<?php echo get_file_uri("assets/images/move.png"); ?>" alt="..." />

<script type="text/javascript">
    var kanbanContainerWidth = "";

    adjustViewHeightWidth = function () {

        if (!$("#kanban-container").length) {
            return false;
        }


        var totalColumns = "<?php echo $total_columns ?>";
        var columnWidth = (335 * totalColumns) + 5;

        if (columnWidth > kanbanContainerWidth) {
            $("#kanban-container").css({width: columnWidth + "px"});
        } else {
            $("#kanban-container").css({width: "100%"});
        }


        //set wrapper scroll
        if ($("#kanban-wrapper")[0].offsetWidth < $("#kanban-wrapper")[0].scrollWidth) {
            $("#kanban-wrapper").css("overflow-x", "scroll");
        } else {
            $("#kanban-wrapper").css("overflow-x", "hidden");
        }


        //set column scroll

        var columnHeight = $(window).height() - $(".kanban-item-list").offset().top - 30;
        if (isMobile()) {
            columnHeight = $(window).height() - 30;
        }

        $(".kanban-item-list").height(columnHeight);

        $(".kanban-item-list").each(function (index) {

            //set scrollbar on column... if requred
            if ($(this)[0].offsetHeight < $(this)[0].scrollHeight) {
                $(this).css("overflow-y", "scroll");
            } else {
                $(this).css("overflow-y", "hidden");
            }

        });
    };


    saveStatusAndSort = function ($item, status) {
        appLoader.show();
        adjustViewHeightWidth();

        var $prev = $item.prev(),
                $next = $item.next(),
                prevSort = 0, nextSort = 0, newSort = 0,
                step = 100000, stepDiff = 500,
                id = $item.attr("data-id");

        if ($prev && $prev.attr("data-sort")) {
            prevSort = $prev.attr("data-sort") * 1;
        }

        if ($next && $next.attr("data-sort")) {
            nextSort = $next.attr("data-sort") * 1;
        }


        if (!prevSort && nextSort) {
            //item moved at the top
            newSort = nextSort - stepDiff;

        } else if (!nextSort && prevSort) {
            //item moved at the bottom
            newSort = prevSort + step;

        } else if (prevSort && nextSort) {
            //item moved inside two items
            newSort = (prevSort + nextSort) / 2;

        } else if (!prevSort && !nextSort) {
            //It's the first item of this column
            newSort = step * 100; //set a big value for 1st item
        }

        $item.attr("data-sort", newSort);


        $.ajax({
            url: '<?php echo_uri("proposals/save_proposal_sort_and_status") ?>',
            type: "POST",
            data: {id: id, sort: newSort, proposal_status_id: status},
            success: function () {
                appLoader.hide();

                if (isMobile()) {
                    adjustViewHeightWidth();
                }
            }
        });

    };



    $(document).ready(function () {
        kanbanContainerWidth = $("#kanban-container").width();

        if (isMobile() && window.scrollToKanbanContent) {
            window.scrollTo(0, 220); //scroll to the content for mobile devices
            window.scrollToKanbanContent = false;
        }

        var isChrome = !!window.chrome && !!window.chrome.webstore;


        $(".kanban-item-list").each(function (index) {
            var id = this.id;

            var options = {
                animation: 150,
                group: "kanban-item-list",
                onAdd: function (e) {
                    //moved to another column. update bothe sort and status
                    saveStatusAndSort($(e.item), $(e.item).closest(".kanban-item-list").attr("data-proposal_status_id"));
                },
                onUpdate: function (e) {
                    //updated sort
                    saveStatusAndSort($(e.item));
                }
            };

            //apply only on chrome because this feature is not working perfectly in other browsers.
            if (isChrome) {
                options.setData = function (dataTransfer, dragEl) {
                    var img = document.createElement("img");
                    img.src = $("#move-icon").attr("src");
                    img.style.opacity = 1;
                    dataTransfer.setDragImage(img, 5, 10);
                };

                options.ghostClass = "kanban-sortable-ghost";
                options.chosenClass = "kanban-sortable-chosen";
            }

            Sortable.create($("#" + id)[0], options);
        });


        adjustViewHeightWidth();



    });

    $(window).resize(function () {
        adjustViewHeightWidth();
    });

</script>
