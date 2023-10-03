<li class="js-proposals-cookie-tab <?php echo ($active_tab == 'proposals_kanban') ? 'active' : ''; ?>" data-tab="proposals_kanban"><a href="<?php echo_uri('proposals/all_proposals_kanban/'); ?>" ><?php echo app_lang('kanban'); ?></a></li>
<script>
    var selectedTab = getCookie("selected_proposals_tab_" + "<?php echo $login_user->id; ?>");
    if (selectedTab && selectedTab !== "<?php echo $active_tab ?>" && selectedTab === "proposals_kanban") {
       window.location.href = "<?php echo_uri('proposals/all_proposals_kanban'); ?>";
    }

    //save the selected tab in browser cookie
    $(document).ready(function () {
        $(".js-proposals-cookie-tab").click(function () {
            var tab = $(this).attr("data-tab");
            if (tab) {
                setCookie("selected_proposals_tab_" + "<?php echo $login_user->id; ?>", tab);
            }
        });
    });
</script>