<div class="bg-white  p15 no-border m0 rounded-bottom">
    <span><?php echo app_lang("status") . ": " . $estimate_status_label; ?></span>
    <span class="ml15">
        <?php
        if ($estimate_info->is_lead) {
            echo app_lang("lead") . ": ";
            echo (anchor(get_uri("leads/view/" . $estimate_info->client_id), $estimate_info->company_name));
        } else {
            echo app_lang("client") . ": ";
            echo (anchor(get_uri("clients/view/" . $estimate_info->client_id), $estimate_info->company_name));
        }
        ?>
    </span>
    <span class="ml15"><?php
        echo app_lang("last_email_sent") . ": ";
        echo (is_date_exists($estimate_info->last_email_sent_date)) ? format_to_date($estimate_info->last_email_sent_date, FALSE) : app_lang("never");
        ?>
    </span>
    <?php if (!$estimate_info->estimate_request_id == 0) {
        ?>
        <span class="ml15">
            <?php
            echo app_lang("estimate_request") . ": ";
            echo (anchor(get_uri("estimate_requests/view_estimate_request/" . $estimate_info->estimate_request_id), app_lang('estimate_request') . " - " . $estimate_info->estimate_request_id));
            ?>
        </span>
        <?php
    }
    ?>
    <span class="ml15"><?php
        if ($estimate_info->project_id) {
            echo app_lang("project") . ": ";
            echo (anchor(get_uri("projects/view/" . $estimate_info->project_id), $estimate_info->project_title));
        }
        ?>
    </span>
    <div class="mt-5">
        <?php
            $files1 = is_dir("./files/timeline_files/estimates/" . $estimate_info->id) ? scandir("./files/timeline_files/estimates/" . $estimate_info->id) : [];
        ?>
        <?php if(count($files1) > 0): ?>
            <i>Arquivos adicionais anexados anteriormente: </i>
            <div style="display: flex;width: 100%;flex-wrap: wrap;">
                <?php foreach($files1 as $file): ?>
                    <?php if($file!= 'index.html' && isset($file) && $file != '..' && $file != '.' && !empty(trim($file))): ?>
                        <div style="display: flex;justify-content: center;align-items: center;">
                            <?php echo anchor(get_uri("files/timeline_files/estimates/" . $estimate_info->id . '/' . $file), "<i data-feather='eye' class='icon-16'></i> " . $file, array("title" => app_lang('see_file') . ': ' . $file, "target" => "_blank", "style" => "border: 1px solid lightgrey;border-radius: 5px;width: 200px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;", "class" => "p-2 mb-1")); ?>
                            <?php echo ajax_anchor(get_uri("estimate/delete_file/" . $estimate_info->id. '/' . $file), '<i data-feather="trash" class="icon-16"></i> Excluir', array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>