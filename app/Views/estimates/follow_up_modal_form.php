
<div id="estimate-dropzone" class="post-dropzone d-flex">
    <?php echo form_open(get_uri("estimates/follow_up"), array("id" => "estimate-form", "class" => "general-form  ".(( $model_info->id ) ? ' col-md-7' : ' col-md-12'), "role" => "form")); ?>
    <div class="modal-body">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
            <div class="form-group">
                <div class="row">
                    <label for="estimate_date" class=" col-md-3"><?php echo app_lang('estimate_date'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "estimate_date",
                            "name" => "estimate_date",
                            "value" => $model_info->estimate_date,
                            "class" => "form-control",
                            "placeholder" => app_lang('estimate_date'),
                            "autocomplete" => "off"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="assigned_to" class=" col-md-3"><?php echo app_lang('assign_to'); ?></label>
                    <div class="col-md-9" id="dropdown-apploader-section">
                        <?php
                        echo form_input(array(
                            "id" => "assigned_to",
                            "name" => "assigned_to",
                            "value" => $model_info->assigned_to,
                            "class" => "form-control",
                            "placeholder" => app_lang('assign_to')
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="collaborators" class=" col-md-3"><?php echo app_lang('collaborators'); ?></label>
                    <div class="col-md-9" id="dropdown-apploader-section">
                        <?php
                        echo form_input(array(
                            "id" => "collaborators",
                            "name" => "collaborators",
                            "value" => $model_info->collaborators,
                            "class" => "form-control",
                            "placeholder" => app_lang('collaborators')
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div id="link-of-new-view" class="hide">
            <?php
            echo modal_anchor(get_uri("estimates/follow_up"), "", array("data-modal-xl" => "1"));
            ?>
        </div>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button id="save-and-show-button" type="button" class="btn btn-info text-white"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div> 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>


<script type="text/javascript">

    $(document).ready(function () {

        $("#save-and-show-button").click(function () {
            window.showAddNewModal = true;
            $(this).trigger("submit");
        });
    
        var cod =  $("#estimate_number").val();
        $("#estimate-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                // $("#save_and_show_value").append(result.save_and_show_link);

                if (window.showAddNewModal) {
                    var $estimateViewLink = $("#link-of-new-view").find("a");

                    //save and show
                    $estimateViewLink.attr("data-action-url", "<?php echo get_uri("estimates/follow_up"); ?>");
                    $estimateViewLink.attr("data-title", 'Adicionar Follow Up');
                    $estimateViewLink.attr("data-act", "ajax-modal");
                    $estimateViewLink.attr("data-post-id", result.id);
                    $estimateViewLink.trigger("click");
                } else {
                   
                }   
            },
            onAjaxSuccess: function (result) {
               location.reload()
            }
        });

        $("#estimate-form .select2").select2();
        setDatePicker("#estimate_date");
        
    });
</script>