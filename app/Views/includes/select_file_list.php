<?php
if (isset($files) && $files) {
    $files = unserialize($files);
    if (count($files)) {
        $image_only = isset($image_only) ? true : false;
        $timeline_file_path = get_setting("timeline_file_path");

        foreach ($files as $file) {
            $file_name = get_array_value($file, "file_name");
            $created_at = get_array_value($file, "created_at");

            if ($image_only && is_viewable_image_file($file_name)) {
                $thumbnail = get_source_url_of_file($file, $timeline_file_path, "thumbnail");
                echo "<div class='col-md-2 col-sm-6 pr0 saved-file-item-container'><div style='background-image: url($thumbnail)' class='edit-image-file mb15' ></div><input type='checkbox' name='attachable_files[]' value='".serialize($file)."'/></div>";
            } else {
                $file_icon = get_file_icon(strtolower(pathinfo(get_array_value($file, "file_name"), PATHINFO_EXTENSION)));
                $path =  get_uri("estimates/download_file/" . get_array_value($file, "file_id"));
                $file_index =  get_array_value($file, "file_index");
                $max_length = 30;
                $adjusted_file_name = (strlen(remove_file_prefix($file_name)) > $max_length) ? substr(remove_file_prefix($file_name), 0, $max_length) . '...' : remove_file_prefix($file_name);
                    echo "
                    <div class='box saved-file-item-container'>
                        <div title='".remove_file_prefix($file_name)."' class='box-content w80p pt5 pb5'>
                            <label for='".serialize($file)."'><div data-feather='$file_icon' class='mr10 float-start'></div>" . ((($type == 2) ? ('<b>Revisão ' . $file_index . '</b> - ') : '') . $adjusted_file_name ) . "</label>
                            
                        </div>  
                        <div class='box-content w20p text-right'><input type='checkbox' name='attachable_files[]' id='".serialize($file)."' value='".serialize($file)."'/><a title='Ver' href='$path' class='p5 dark' data-file_name='$file_name'><span data-feather='download' class='icon-16'></span></a></div> 
                    </div>";
            }
        }
    }
}