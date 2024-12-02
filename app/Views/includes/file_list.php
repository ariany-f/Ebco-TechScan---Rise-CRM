<?php

if (isset($files) && $files) {
    $files = unserialize($files);
    if (count($files)) {
        $image_only = isset($image_only) ? true : false;
        $timeline_file_path = get_setting("timeline_file_path");

        foreach ($files as $file) {
            $file_name = get_array_value($file, "file_name");

            if ($image_only && is_viewable_image_file($file_name)) {
                $thumbnail = get_source_url_of_file($file, $timeline_file_path, "thumbnail");
                echo "<div class='col-md-2 col-sm-6 pr0 saved-file-item-container'><div style='background-image: url($thumbnail)' class='edit-image-file mb15' ><a href='#' class='delete-saved-file' data-file_name='$file_name'><span data-feather='x' class='icon-16'></span></a></div></div>";
            } else {
                
                $file_icon = get_file_icon(strtolower(pathinfo(get_array_value($file, "file_name"), PATHINFO_EXTENSION)));
                $max_length = 50;
                $adjusted_file_name = (strlen(($file_name)) > $max_length) ? substr(($file_name), 0, $max_length) . '...' : ($file_name);
                echo "<div class='box saved-file-item-container'><div title='".$file_name."' class='box-content w80p pt5 pb5'><div data-feather='$file_icon' class='mr10 float-start'></div>" . ($adjusted_file_name) . "</div> <div class='box-content w20p text-right'><a href='#' class='delete-saved-file p5 dark' data-file_name='$file_name'><span data-feather='x' class='icon-16'></span></a></div> </div>";
            }
        }
    }
}
