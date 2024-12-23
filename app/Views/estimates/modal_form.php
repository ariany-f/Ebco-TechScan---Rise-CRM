
<div id="estimate-dropzone" class="post-dropzone">
<?php echo form_open(get_uri("estimates/save"), array("id" => "estimate-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="estimate_request_id" value="<?php echo $estimate_request_id; ?>" />

        <?php if ($is_clone || $order_id || $contract_id || $proposal_id) { ?>
            <input type="hidden" name="is_clone" value="1" />
            <input type="hidden" name="discount_amount" value="<?php echo $model_info->discount_amount; ?>" />
            <input type="hidden" name="discount_amount_type" value="<?php echo $model_info->discount_amount_type; ?>" />
            <input type="hidden" name="discount_type" value="<?php echo $model_info->discount_type; ?>" />
        <?php } ?>
        <div class="form-group">
            <div class="row">
                <label for="is_bidding" class="col-md-3 d-flex align-items-center"> <?php echo app_lang('is_bidding'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_checkbox(
                        "is_bidding",
                        "1", 
                        $model_info->is_bidding ?? false, 
                        "id='is_bidding'"
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group bidding hide">
            <div class="row">
                <label for="uasg" class="col-md-3 d-flex align-items-center"> <?php echo app_lang('uasg'); ?></label>
                <div class=" col-md-9">
                    <?php
                     echo form_input(
                        array(
                            "id" => "uasg",
                            "name" => "uasg",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => app_lang('uasg')
                        )
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label class=" col-md-3" for="estimate_number" class="<?php echo $label_column; ?>"><?php echo app_lang('estimate_number'); ?>
                    <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo "Gerado automáticamente" ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                </label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "estimate_number",
                        "name" => "estimate_number",
                        "value" => $model_info->id ? ($model_info->estimate_number ? $model_info->estimate_number : "Rev") : $next_id,
                        "disabled" => true,
                        "class" => "form-control",
                        "placeholder" => app_lang('estimate_number')
                    ));
                    ?>
                </div>
            </div>
        </div>
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
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="valid_until" class=" col-md-3"><?php echo app_lang('valid_until'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "valid_until",
                        "name" => "valid_until",
                        "value" => $model_info->valid_until,
                        "class" => "form-control",
                        "placeholder" => app_lang('valid_until'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "data-rule-greaterThanOrEqual" => "#estimate_date",
                        "data-msg-greaterThanOrEqual" => app_lang("end_date_must_be_equal_or_greater_than_start_date")
                    ));
                    ?>
                </div>
            </div>
        </div>
        <?php if (count($companies_dropdown) > 1) { ?>
            <div class="form-group">
                <div class="row">
                    <label for="company_id" class=" col-md-3"><?php echo app_lang('company_owner'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "company_id",
                            "name" => "company_id",
                            "value" => $model_info->company_id,
                            "class" => "form-control",
                            "placeholder" => app_lang('company_owner')
                        ));
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if ($client_id) { ?>
            <input type="hidden" name="estimate_client_id" value="<?php echo $client_id; ?>" />
        <?php } else { ?>
            <div class="form-group">
                <div class="row">
                    <label for="estimate_client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("estimate_client_id", $clients_dropdown, array($model_info->client_id), "class='select2 validate-hidden' id='estimate_client_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="form-group hide ">
            <div class="row">
                <label for="estimate_type_id" class=" col-md-3"><?php echo app_lang('estimate_type'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_dropdown("estimate_type_id", $estimate_type_dropdown, array($model_info->estimate_type_id), "class='select2 estimate-type-select2'");
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 d-flex" style="gap: 5px;">
            <div class="form-group col-md-6">
                <div class="row">
                    <label for="margem" class=" col-md-6 d-flex align-items-center"><?php echo app_lang('margem') ; ?></label>
                    <div class=" col-md-6">
                        <?php
                        echo form_input(array(
                            "id" => "margem",
                            "name" => "margem",
                            "value" => $model_info->margem ? $model_info->margem : "",
                            "class" => "percent form-control",
                            "placeholder" => app_lang('margem')
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
                <div class="row">
                    <label for="prazo_em_dias" class=" col-md-5 d-flex align-items-center"><?php echo app_lang('prazo_em_dias') ; ?></label>
                    <div class=" col-md-7">
                        <?php
                        echo form_input(array(
                            "id" => "prazo_em_dias",
                            "name" => "prazo_em_dias",
                            "value" => $model_info->prazo_em_dias ? $model_info->prazo_em_dias : "",
                            "class" => "form-control",
                            "type" => "number",
                            "placeholder" => app_lang('prazo_em_dias')
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="estimate_note" class=" col-md-3"><?php echo app_lang('payment_conditions') ; ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "estimate_note",
                        "name" => "estimate_note",
                        "value" => $model_info->note ? process_images_from_content($model_info->note, false) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('payment_conditions'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
        </div>

        <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 

        <?php if ($is_clone) { ?>
            <div class="form-group">
                <div class="row">
                    <label for="copy_items"class=" col-md-12">
                        <?php
                        echo form_checkbox("copy_items", "1", true, "id='copy_items' disabled='disabled' class='float-start mr15 form-check-input'");
                        ?>    
                        <?php echo app_lang('copy_items'); ?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="copy_discount"class=" col-md-12">
                        <?php
                        echo form_checkbox("copy_discount", "1", true, "id='copy_discount' disabled='disabled' class='float-start mr15 form-check-input'");
                        ?>    
                        <?php echo app_lang('copy_discount'); ?>
                    </label>
                </div>
            </div>
        <?php } ?> 

        <?php if ($contract_id) { ?>
            <input type="hidden" name="contract_id" value="<?php echo $contract_id; ?>" />
            <div class="form-group">
                <div class="row">
                    <label for="contract_id_checkbox" class=" col-md-12">
                        <input type="hidden" name="copy_items_from_contract" value="<?php echo $contract_id; ?>" />
                        <?php
                        echo form_checkbox("contract_id_checkbox", $contract_id, true, " class='float-start form-check-input' disabled='disabled'");
                        ?>    
                        <span class="float-start ml15"> <?php echo app_lang('include_all_items_of_this_contract'); ?> </span>
                    </label>
                </div>
            </div>
        <?php } ?>

        <?php if ($proposal_id) { ?>
            <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>" />
            <div class="form-group">
                <div class="row">
                    <label for="proposal_id_checkbox" class=" col-md-12">
                        <input type="hidden" name="copy_items_from_proposal" value="<?php echo $proposal_id; ?>" />
                        <?php
                        echo form_checkbox("proposal_id_checkbox", $proposal_id, true, " class='float-start form-check-input' disabled='disabled'");
                        ?>    
                        <span class="float-start ml15"> <?php echo app_lang('include_all_items_of_this_proposal'); ?> </span>
                    </label>
                </div>
            </div>
        <?php } ?>

        <?php if ($order_id) { ?>
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
            <div class="form-group">
                <div class="row">
                    <label for="order_id_checkbox" class=" col-md-12">
                        <input type="hidden" name="copy_items_from_order" value="<?php echo $order_id; ?>" />
                        <?php
                        echo form_checkbox("order_id_checkbox", $order_id, true, " class='float-start form-check-input' disabled='disabled'");
                        ?>    
                        <span class="float-start ml15"> <?php echo app_lang('include_all_items_of_this_order'); ?> </span>
                    </label>
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="col-md-12">
                <?php
                echo view("includes/file_list", array("files" => $model_info->files));
                ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <strong>Arquivos</strong><br/>
                Aqui você pode anexar os arquivos de memória de calculo, levantamento de custos e proposta.<br/>
                <i>O arquivo da proposta deve ter o titulo iniciado em "Proposta_"</i>
            </div>
        </div>
    </div>
    <?php echo view("includes/dropzone_preview"); ?>
</div>

<div class="modal-footer">
    <button class="btn btn-default upload-file-button float-start me-auto btn-sm round" type="button" style="color:#7988a2"><i data-feather='camera' class='icon-16'></i> <?php echo app_lang("upload_file"); ?></button>
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
</div> 
<?php echo form_close(); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript">
  
    $(document).ready(function () {

        $("#is_bidding").on('change', function() {
            var is_bidding = $('input[name=is_bidding]:checked').length > 0;
            if(is_bidding) {
                $(".form-group.bidding").removeClass('hide');
            }
            else
            {
                $(".form-group.bidding").addClass('hide');
            }
        })

        var request_made = false;

        $("#uasg").on('keyup', function() {
            var uasg = $("#uasg").val();
            var year = ((new Date).getFullYear());
            if(uasg.length  >= 5 && (!request_made))
            {
                $.ajax({
                    url: AppHelper.baseUrl + 'estimates/uasg/' + uasg,
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        appLoader.show()
                        request_made = true;
                        if(response.resultado.length) {
                            var cnpj = response.resultado[0].cnpjCpfOrgao;
                            var cnpjVinculado = response.resultado[0].cnpjCpfOrgaoVinculado;
                            var company_name = response.resultado[0].nomeUasg;
                            var social_name = response.resultado[0].nomeUnidadePolo;
                            $.ajax({
                                url: "<?php echo get_uri('search/get_search_suggestion/'); ?>",
                                data: {search: cnpjVinculado, search_field: 'client_lead'},
                                cache: false,
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    if(response.length)
                                    {
                                        $("#estimate_client_id").val(response[0].value).change();
                                    } 
                                    else {
                                        $.confirm({
                                            title: 'Cadastro de cliente',
                                            content: 'Cliente não cadastrado: '+cnpjVinculado+'. Cadastrar?',
                                            buttons: {
                                                cadastrar: function () {
                                                    var data = {
                                                        "custom_field_12": company_name,
                                                        "company_name" : social_name,
                                                        "matriz_cnpj": cnpj,
                                                        "type": "organization",
                                                        "setor": "public",
                                                        "is_lead": '1',
                                                        "lead_status_id": 2,
                                                        "cnpj": cnpjVinculado
                                                    }
                                                    
                                                    $.ajax({
                                                        url: AppHelper.baseUrl + 'clients/save',
                                                        cache: false,
                                                        type: 'POST',
                                                        dataType: "json",
                                                        data: data,
                                                        success: function (response) {
                                                            $.alert({
                                                                title: 'Alerta!',
                                                                content: 'Cadastrado com sucesso, por favor feche o modal e refaça a operação!',
                                                            });
                                                        }
                                                    });
                                                },
                                                nao: function () {
                                                    $("#uasg").val('')
                                                }
                                            }
                                        });
                                    }
                                    appLoader.hide()
                                    request_made = false;
                                },
                                error: function() {
                                    request_made = false;
                                    appLoader.hide()
                                }
                            });
                        }
                        else{
                            $.alert({
                                title: 'Alerta!',
                                content: 'Não foram encontrados resultados para este código!',
                            });
                            request_made = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        // O que fazer em caso de erro
                        console.error("Erro na requisição:", error);
                        appLoader.hide()
                    }
                });
            }
        })

        var cod =  $("#estimate_number").val();
        $("#estimate-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    <?php if( $model_info->id ): ?>
                        if(!<?php echo $model_info->id ?>)
                        {
                            window.location = "<?php echo site_url('estimates/view'); ?>/" + result.id;
                        }
                        else
                        {
                            var oTable = $('#monthly-estimate-table').dataTable();
                            // to reload
                            oTable.api().ajax.reload();
                            var oTable = $('#yearly-estimate-table').dataTable();
                            // to reload
                            oTable.api().ajax.reload();
                        }
                    <?php else: ?>
                        var oTable = $('#monthly-estimate-table').dataTable();
                        // to reload
                        oTable.api().ajax.reload();
                        var oTable = $('#yearly-estimate-table').dataTable();
                        // to reload
                        oTable.api().ajax.reload();
                    <?php endif; ?>
                }
            }
        });
        $("#estimate-form .tax-select2").select2();
        $("#estimate-form .estimate-type-select2").select2();
        $("#estimate_client_id").select2();

        setTimeout(() => {
            $("input[name='custom_field_5']").mask('#.###.##0,00', { reverse: true });
            $("input[name='margem']").mask('##%', { reverse: true });
        }, 1000);

        $("#company_id").select2({data: <?php echo json_encode($companies_dropdown); ?>});

        $("#company_id").on('change', function() {

            /** ZK TECO SEGUE O PADRÃO ZK_{ANO}{PROXINDEX} */
            if(this.value == 3) {
                $.ajax({
                    url: AppHelper.baseUrl + 'estimates/next_zk_id',
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        $("#estimate_number").val("ZK_" + response)
                    }
                });
            }
            else
            {
                $("#estimate_number").val(cod)
            }
        })

        setDatePicker("#estimate_date, #valid_until");

        var uploadUrl = "<?php echo get_uri("estimates/upload_estimate_file"); ?>";
        var validationUrl = "<?php echo get_uri("estimates/validate_file"); ?>";

        var dropzone = attachDropzoneWithForm("#estimate-dropzone", uploadUrl, validationUrl);

    });
</script>