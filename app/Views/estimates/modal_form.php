
<div id="estimate-dropzone" class="post-dropzone d-flex">
    <?php echo form_open(get_uri("estimates/save"), array("id" => "estimate-form", "class" => "general-form  ".(( $model_info->id ) ? ' col-md-7' : ' col-md-12'), "role" => "form")); ?>
    <div class="modal-body">
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
                            "value" => ($model_info->id ? ($model_info->estimate_number ? $model_info->estimate_number : "Rev") : $next_id),
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
                            "autocomplete" => "off"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="prazo_em_dias" class=" col-md-3"><?php echo app_lang('prazo_em_dias') ; ?></label>
                    <div class=" col-md-9">
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
            <div class="form-group">
                <div class="row">
                    <label for="margem" class=" col-md-3"><?php echo app_lang('margem') ; ?></label>
                    <div class=" col-md-9">
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
            <!-- <div class="form-group">
                <div class="row">
                    <label for="estimate_note" class=" col-md-3"><?php //echo app_lang('payment_conditions') ; ?></label>
                    <div class=" col-md-9">
                        <?php
                        // echo form_textarea(array(
                        //     "id" => "estimate_note",
                        //     "name" => "estimate_note",
                        //     "value" => $model_info->note ? process_images_from_content($model_info->note, false) : "",
                        //     "class" => "form-control",
                        //     "placeholder" => app_lang('payment_conditions'),
                        //     "data-rich-text-editor" => true
                        // ));
                        ?>
                    </div>
                </div>
            </div> -->

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
        <div id="link-of-new-view" class="hide">
            <?php
            echo modal_anchor(get_uri("estimates/modal_form"), "", array("data-modal-xl" => "1"));
            ?>
        </div>
        <button class="btn btn-default upload-file-button float-start me-auto btn-sm round" type="button" style="color:#7988a2"><i data-feather='camera' class='icon-16'></i> <?php echo app_lang("upload_file"); ?></button>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button id="save-and-show-button" type="button" class="btn btn-info text-white"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
    <?php echo form_close(); ?>

    <?php if($model_info->id) : ?>
        <!--checklist-->
        <?php echo form_open(get_uri("estimates/save_vale_item"), array("id" => "estimate_value_form", "class" => "general-form  " . (( $model_info->id ) ? ' col-md-5' : ' hide'), "role" => "form")); ?>
        <div class="col-md-12 container">
            <div class="form-group">
                <div class="row">
                    <label for="currency" class="<?php echo $label_column; ?>"><?php echo app_lang('destiny') . ' ' . app_lang('currency'); ?></label>
                    <div class="<?php echo $field_column; ?>">
                        <?php
                        echo form_input(array(
                            "id" => "currency",
                            "name" => "currency",
                            "value" => $model_info->currency,
                            "class" => "form-control",
                            "placeholder" => app_lang('keep_it_blank_to_use_default') . " (" . get_setting("default_currency") . ")"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <b>Moeda de origem padrão: BRL</b>
                    <small>Caso selecione como <i>destino</i> a moeda <b>BRL</b> então automaticamente a moeda de <i>origem</i> é alterada para <b>USD</b></small>
                    <div style="margin-top: 2px;" class="<?php echo $field_column; ?>">
                        <button id="convert" type="button" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('convert'); ?></button>
                    </div>
                </div>
            </div>

            <div class="pb10 pt10 mb10">
                <strong class="float-start mr10"><?php echo app_lang("estimate_value"); ?></strong>
            </div>
            <input type="hidden" name="estimate_id" value="<?php echo ($model_info->id); ?>" />

            <div class="estimate-value-items" id="estimate-value-items">

            </div>
            <div class="form-group d-flex col-md-12" style="gap:2px">
                <div class="mt5 p0  col-md-6">
                    <?php
                    echo form_input(array(
                        "id" => "estimate-value-add-item-description",
                        "name" => "estimate-value-add-item-description",
                        "class" => "form-control",
                        "placeholder" => app_lang('add_value_item_description'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "autocomplete" => "off"
                    ));
                    ?>
                </div>
                <div class="mt5 p0 col-md-6">
                    <?php
                    echo form_input(array(
                        "id" => "estimate-value-add-item",
                        "name" => "estimate-value-add-item",
                        "class" => "form-control",
                        "placeholder" => app_lang('add_value_item'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "autocomplete" => "off"
                    ));
                    ?>
                </div>
            </div>
            <div class="mb10 p0 estimate_value-options-panel hide">
                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('add'); ?></button>
                <button id="estimate_value-options-panel-close" type="button" class="btn btn-default ms-2"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    <?php endif; ?>
</div> 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>


<script type="text/javascript">

    $(document).ready(function () {

                
        <?php if (isset($currency_dropdown)) { ?>
            if ($('#currency').length) {
                $('#currency').select2({data: <?php echo json_encode($currency_dropdown); ?>});
            }
        <?php } ?>

        $("#convert").on('click', function() {
            var moeda = $("#currency").val();
            var items = $("#estimate-value-items .list-group-item a");
        
            if(!moeda)
            {
                alert('Selecione a moeda para converter!')
                return;
            }
            else
            {
                if(!items.length)
                {
                    alert('Não há itens para converter!')
                    return;
                }
                else
                {
                    items.each(function() {
                        var dataAmount = $(this).data('amount');  // Pegar o valor do atributo data-amount
                        var id = $(this).data('id');
                        var convertedAmount = 0;
                        // Atualizar o item com o valor convertido (pode ser em um texto, por exemplo)
                        $.ajax({
                            url: AppHelper.baseUrl + 'estimates/save_value_convert_item',
                            cache: false,
                            type: 'POST',
                            data: {
                                estimate_value_item_data_amount: dataAmount,
                                currency: moeda,
                                estimate_value_item_id: id
                            },
                            dataType: "json",
                            success: function (response) {
                                
                                if(response.success)
                                {
                                    convertedAmount = response.converted_amount;
                                    var data = new Date(),
                                        dia = data.getDate().toString(),
                                        diaF = (dia.length == 1) ? '0' + dia : dia,
                                        mes = (data.getMonth() + 1).toString(), // +1 pois getMonth começa do 0
                                        mesF = (mes.length == 1) ? '0' + mes : mes,
                                        anoF = data.getFullYear(),
                                        hora = data.getHours().toString(),
                                        horaF = (hora.length == 1) ? '0' + hora : hora,
                                        minuto = data.getMinutes().toString(),
                                        minutoF = (minuto.length == 1) ? '0' + minuto : minuto;

                                    // Formatar data e hora
                                    var fDate = diaF + "/" + mesF + "/" + anoF + " " + horaF + ":" + minutoF;
                                    $("#converted-"+id).text(' -> ' + moeda + convertedAmount.toFixed(2) + ' (' + fDate + ')');
                                }
                            }
                        });
                    })
                }
            }

            
            // //API Moeda
            // var url = "https://economia.awesomeapi.com.br/BRL-"+moeda;

            // // Fazer a requisição para a API
            // $.get(url, function(response) {
                
            //     if (response && response[0] && response[0].ask) {
            //         var conversionRate = parseFloat(response[0].ask); // Taxa de conversão

            //         // Iterar sobre os itens da lista
            //         items.each(function() {
            //             var dataAmount = $(this).data('amount');  // Pegar o valor do atributo data-amount
            //             var id = $(this).data('id');

            //             if (dataAmount) {
            //                 // Converter o valor usando a taxa da API
            //                 var convertedAmount = parseFloat(dataAmount) * conversionRate;
                            
            //                 // Atualizar o item com o valor convertido (pode ser em um texto, por exemplo)
            //                 $.ajax({
            //                     url: AppHelper.baseUrl + 'estimates/save_value_converted_item',
            //                     cache: false,
            //                     type: 'POST',
            //                     data: {
            //                         currency: moeda,
            //                         estimate_value_item_id: id,
            //                         converted_amount: convertedAmount.toFixed(2)
            //                     },
            //                     dataType: "json",
            //                     success: function (response) {
            //                         console.log(response)
            //                     }
            //                 });
            //                 var data = new Date(),
            //                     dia = data.getDate().toString(),
            //                     diaF = (dia.length == 1) ? '0' + dia : dia,
            //                     mes = (data.getMonth() + 1).toString(), // +1 pois getMonth começa do 0
            //                     mesF = (mes.length == 1) ? '0' + mes : mes,
            //                     anoF = data.getFullYear(),
            //                     hora = data.getHours().toString(),
            //                     horaF = (hora.length == 1) ? '0' + hora : hora,
            //                     minuto = data.getMinutes().toString(),
            //                     minutoF = (minuto.length == 1) ? '0' + minuto : minuto;

            //                 // Formatar data e hora
            //                 var fDate = diaF + "/" + mesF + "/" + anoF + " " + horaF + ":" + minutoF;
            //                 $("#converted-"+id).text(' -> ' + moeda + convertedAmount.toFixed(2) + ' (' + fDate + ')');
            //             }
            //         });
            //     } else {
            //         console.error("Erro ao obter a taxa de conversão");
            //     }
            // }).fail(function() {
            //     console.error("Erro na requisição da API.");
            // });
        })

        //send data to show the task after save
        window.showAddNewModal = false;

        $("#save-and-show-button").click(function () {
            window.showAddNewModal = true;
            $(this).trigger("submit");
        });

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

        $("#prazo_em_dias").on('keyup', function() {
            var dias = $(this).val();
            var estimate_date = $("#estimate_date").val();

            // Converter a string de data no formato "DD/MM/YYYY" para um objeto Date
            let partesData = estimate_date.split("/"); // Divide a data

            let dia = parseInt(partesData[0], 10);       // Extrai o dia
            let mes = parseInt(partesData[1], 10) - 1;  // Ajusta para o mês 0 a 11 (dezembro é 11)
            let ano = parseInt(partesData[2], 10);      // Extrai o ano

            // Cria o objeto Date no formato UTC para evitar problemas de fuso horário
            let dataInicial = Date.UTC(ano, mes, dia);

            // Verificar se a data foi criada corretamente
            if (isNaN(dataInicial)) {
                console.error("Data inválida");
                return null;
            }

            // Adiciona os dias à data em milissegundos (1 dia = 24 * 60 * 60 * 1000 milissegundos)
            dataInicial += dias * 24 * 60 * 60 * 1000;

            // Cria uma nova data a partir da soma dos milissegundos
            let novaData = new Date(dataInicial);

            // Formata a nova data para "DD/MM/YYYY"
            let diaFinal = String(novaData.getUTCDate()).padStart(2, '0');
            let mesFinal = String(novaData.getUTCMonth() + 1).padStart(2, '0'); // Ajuste do mês (1 a 12)
            let anoFinal = novaData.getUTCFullYear();
            dateInput = $("#valid_until");
            let dataFinalFormatada = `${diaFinal}/${mesFinal}/${anoFinal}`;
            dateInput.datepicker("setDate", dataFinalFormatada); 
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
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                // $("#save_and_show_value").append(result.save_and_show_link);

                if (window.showAddNewModal) {
                    var $estimateViewLink = $("#link-of-new-view").find("a");

                    //save and show
                    $estimateViewLink.attr("data-action-url", "<?php echo get_uri("estimates/modal_form"); ?>");
                    $estimateViewLink.attr("data-title", 'Editar Proposta');
                    $estimateViewLink.attr("data-act", "ajax-modal");
                    $estimateViewLink.attr("data-post-id", result.id);
                    $estimateViewLink.trigger("click");
                } else {
                   
                }   
            },
            onAjaxSuccess: function (result) {
                
            }
        });

        $("#estimate-form .tax-select2").select2();
        $("#estimate-form .estimate-type-select2").select2();
        $("#estimate_client_id").select2();

        setTimeout(() => {
            $("input[name='custom_field_5']").mask('#.###.##0,00', { reverse: true });
            $("input[name='estimate-value-add-item']").mask('#.###.##0,00', { reverse: true });
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
                        let year = new Date().getFullYear()
                        $("#estimate_number").val("ZK_" + year+response)
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

        //show the items in estimate_value
        $("#estimate-value-items").html(<?php echo $estimate_value_items; ?>);

        //show save & cancel button when the estimate-value-add-item-form is focused
        $("#estimate-value-add-item, #estimate-value-add-item-description").focus(function () {
            $(".estimate_value-options-panel").removeClass("hide");
            $("#estimate-value-add-item-error").removeClass("hide");
            $("#estimate-value-add-item-description-error").removeClass("hide");
        });

        $("#estimate_value-options-panel-close").click(function () {
            $(".estimate_value-options-panel").addClass("hide");
            $("#estimate-value-add-item-error").addClass("hide");
            $("#estimate-value-add-item").val("");

            $("#estimate-value-add-item-description-error").addClass("hide");
            $("#estimate-value-add-item-description").val("");

            $("#estimate-value-add-item").select2("destroy").val("");
            $("#estimate-value-add-item-description").select2("destroy").val("");
            feather.replace();

            $(".estimate_value_button").removeClass("active");
            $("#type-new-item-button").addClass("active");
        });


        var estimate_values = $(".estimate-value-items .checklist-item-row").length;
        $(".delete-estimate-value-item").click(function () {
            estimate_values--;
            $(".estimates_values_count").text(estimate_values);
        });

        var estimate_value_complete = $(".estimate-value-items input:checked").length;
        $(".estimates_values_status_count").text(estimate_value_complete);
        
        $('body').on('click', '[data-act=update-estimate-value-item-status-checkbox]', function () {
            var status_checkbox = $(this).find("input");
            status_checkbox.addClass("inline-loader");

            var id = $(this).attr('data-id');
            var estimate = $(this).attr('data-estimate-id');
            
            $.ajax({
                url: '<?php echo_uri("estimates/save_estimate_value_item_check") ?>/' + estimate,
                type: 'POST',
                dataType: 'json',
                data: {id: id},
                success: function (response) {
                    if (response.success) {
                        $(".checklist-item-row input").attr('checked', false);
                        $(".checklist-item-row a").addClass('text-off');
                        status_checkbox.closest("div").html(response.data); 
                        status_checkbox.attr('checked', true);
                    }
                }
            });
        });

        $("#estimate_value_form").appForm({
            isModal: false,
            onSuccess: function (response) {
                $("#estimate-value-add-item").val("");
                $("#estimate-value-add-item-description").val("");
                $("#estimate-value-add-item").focus();
                $("#estimate-value-items").append(response.data);
            }
        });
    });
</script>