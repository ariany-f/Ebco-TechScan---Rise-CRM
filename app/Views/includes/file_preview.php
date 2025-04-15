<?php
if (!get_setting("disable_google_preview") && ($is_google_drive_file || (!is_localhost() && $is_google_preview_available))) {
    //don't show google preview in localhost
    $src_url = "https://drive.google.com/viewerng/viewer?url=$file_url?pid=explorer&efh=false&a=v&chrome=false&embedded=true";
    if ($is_google_drive_file) {
        $src_url = $file_url;
    }
    ?>

    <iframe id='google-file-viewer' src="<?php echo $src_url; ?>" style="width: 100%; margin: 0; border: 0;"></iframe>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#google-file-viewer").css({height: $(window).height() + "px"});
            $(".app-modal .expand").hide();
        });
    </script>
    <?php
} else if ($is_image_file) {
    ?>
    <img src="<?php echo $file_url; ?>" />
    <?php
} else if ($is_excel_file) {
    // Visualização de Arquivos Excel (XLSX/XLS/CSV)
    ?>
    <div id="excel-preview-container" style="width: 100%; height: 80vh; overflow: auto;">
        <div id="excel-data-table"></div>
    </div>
    
    <!-- Inclui a biblioteca SheetJS (xlsx.js) para análise do arquivo Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    $(document).ready(function() {
        // Faz o download do arquivo XLSX via AJAX (convertendo para JSON)
        fetch('<?php echo $file_url; ?>')
            .then(response => response.arrayBuffer())
            .then(buffer => {
                const data = new Uint8Array(buffer);
                const workbook = XLSX.read(data, { type: 'array' });
                
                // Pega a primeira planilha
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                
                // Converte para HTML
                const html = XLSX.utils.sheet_to_html(firstSheet);
                
                // Exibe na página
                document.getElementById('excel-data-table').innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar o arquivo Excel:', error);
                $('#excel-preview-container').html('<p class="text-danger"><?php echo app_lang("file_preview_not_available"); ?></p>');
            });
    });
    </script>
    <?php
} else if (($is_viewable_video_file && !$is_google_drive_file) || (isset($is_iframe_preview_available) && $is_iframe_preview_available)) {
    //show with default iframe
    ?>

    <iframe id="iframe-file-viewer" src="<?php echo $file_url ?>" style="width: 100%; margin: 0; border: 0; height: 100%;"></iframe>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#iframe-file-viewer").closest("div.app-modal-content-area").css({"height": "100%", display: "table", width: "100%"});
        });
    </script>
    <?php
} else {
    //Preview is not avaialble. 
    echo app_lang("file_preview_is_not_available") . "<br />";
    echo anchor($file_url, app_lang("download"));
}
?>

<script>
    function initScrollbarOnCommentContainer() {
        if ($("#file-preview-comment-container").height() > ($(window).height() - 300)) {
            initScrollbar('#file-preview-comment-container', {
                setHeight: $(window).height() - 300
            });
        }
    }
</script>