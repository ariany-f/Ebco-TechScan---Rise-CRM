<script>
    $(document).ready(function () {
        //load all related data of the selected project
        $("#estimate_id").select2().on("change", function () {
            var estimateId = $(this).val();
            if ($(this).val()) {
                $('#assigned_to').select2("destroy");
                $("#assigned_to").hide();
                $('#collaborators').select2("destroy");
                $("#collaborators").hide();
                appLoader.show({container: "#dropdown-apploader-section", zIndex: 1});
                $.ajax({
                    url: "<?php echo get_uri('estimate/get_all_related_data_of_selected_estimate') ?>" + "/" + estimateId,
                    dataType: "json",
                    success: function (result) {
                        console.log(result)
                        $("#assigned_to").show().val("");
                        $('#assigned_to').select2({data: result.assign_to_dropdown});
                        $("#collaborators").show().val("");
                        $('#collaborators').select2({multiple: true, data: result.collaborators_dropdown});
                        appLoader.hide();
                    }
                });
            }
        });

        //intialized select2 dropdown for first time
        $("#collaborators").select2({multiple: true, data: <?php echo json_encode($collaborators_dropdown); ?>});
        $('#assigned_to').select2({data: <?php echo json_encode($assign_to_dropdown); ?>});
    });
</script>