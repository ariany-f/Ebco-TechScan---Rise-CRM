<input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<div class="form-group">
    <div class="row">
        <label for="type" class="<?php echo $label_column; ?>"><?php echo app_lang('type'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_radio(array(
                "id" => "type_organization",
                "name" => "account_type",
                "class" => "form-check-input account_type",
                "data-msg-required" => app_lang("field_required"),
                    ), "organization", ($model_info->type === "organization") ? true : (($model_info->type !== "person") ? true : false));
            ?>
            <label for="type_organization" class="mr15"><?php echo app_lang('organization'); ?></label>
            <?php
            echo form_radio(array(
                "id" => "type_person",
                "name" => "account_type",
                "class" => "form-check-input account_type",
                "data-msg-required" => app_lang("field_required"),
                    ), "person", ($model_info->type === "person") ? true : false);
            ?>
            <label for="type_person" class=""><?php echo app_lang('person'); ?></label>
        </div>
    </div>
</div>

<?php if ($model_info->id) { ?>
    <div class="form-group">
        <div class="row">
            <?php if ($model_info->type == "person") { ?>
                <label for="name" class="<?php echo $label_column; ?> company_name_section"><?php echo app_lang('name'); ?></label>
            <?php } else { ?>
                <label for="company_name" class="<?php echo $label_column; ?> company_name_section"><?php echo app_lang('company_name'); ?></label>
            <?php } ?>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => ($model_info->type == "person") ? "name" : "company_name",
                    "name" => "company_name",
                    "value" => $model_info->company_name,
                    "class" => "form-control company_name_input_section",
                    "placeholder" => app_lang('company_name'),
                    "autofocus" => true,
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
        </div>
    </div>

    <?php if ($model_info->type != "person") { ?>
        <div class="form-group cnpj">
            <div class="row">
                <label for="cnpj" class="<?php echo $label_column; ?>"><?php echo app_lang('cnpj'); ?>
                    <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo "Só é obrigatório para filiais que possuem cnpj proprio" ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                </label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "cnpj",
                        "name" => "cnpj",
                        "maxlength"=>"18",
                        "value" => $model_info->cnpj,
                        "class" => "form-control",
                        "placeholder" => app_lang('cnpj'),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group cnpj">
            <div class="row">
                <label for="matriz_cnpj" class="<?php echo $label_column; ?>"><?php echo app_lang('matriz_cnpj'); ?>
                    <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo "Caso seja filial, preencha o cnpj da matriz" ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                </label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "matriz_cnpj",
                        "name" => "matriz_cnpj",
                        "maxlength"=>"18",
                        "value" => $model_info->matriz_cnpj,
                        "class" => "form-control",
                        "placeholder" => app_lang('matriz_cnpj'),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group cnpj">
            <div class="row">
                <label for="state_subscription" class=" col-md-3"><?php echo app_lang('state_subscription'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "state_subscription",
                        "name" => "state_subscription",
                        "value" => $model_info->state_subscription,
                        "class" => "form-control",
                        "placeholder" => app_lang('state_subscription'),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group cnpj">
            <div class="row">
                <label for="city_subscription" class=" col-md-3"><?php echo app_lang('city_subscription'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "city_subscription",
                        "name" => "city_subscription",
                        "value" => $model_info->city_subscription,
                        "class" => "form-control",
                        "placeholder" => app_lang('city_subscription'),
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>

<?php } else { ?>
    <div class="form-group">
        <div class="row">
            <label for="company_name" class="<?php echo $label_column; ?> company_name_section"><?php echo app_lang('company_name'); ?></label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "company_name",
                    "name" => "company_name",
                    "value" => $model_info->company_name,
                    "class" => "form-control company_name_input_section",
                    "placeholder" => app_lang('company_name'),
                    "autofocus" => true,
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group cnpj">
        <div class="row">
            <label for="cnpj" class="<?php echo $label_column; ?>"><?php echo app_lang('cnpj'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo "Só é obrigatório para filiais que possuem cnpj proprio" ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class=" col-md-9">
                <?php
                echo form_input(array(
                    "id" => "cnpj",
                    "name" => "cnpj",
                    "maxlength"=>"18",
                    "value" => $model_info->cnpj,
                    "class" => "form-control",
                    "placeholder" => app_lang('cnpj'),
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group cnpj">
        <div class="row">
            <label for="matriz_cnpj" class="<?php echo $label_column; ?>"><?php echo app_lang('matriz_cnpj'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo "Caso seja filial, preencha o cnpj da matriz" ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class=" col-md-9">
                <?php
                echo form_input(array(
                    "id" => "matriz_cnpj",
                    "name" => "matriz_cnpj",
                    "maxlength"=>"18",
                    "value" => $model_info->matriz_cnpj,
                    "class" => "form-control",
                    "placeholder" => app_lang('matriz_cnpj'),
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group cnpj">
        <div class="row">
            <label for="setor" class="<?php echo $label_column; ?>"><?php echo app_lang('setor'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('public') . ' ou ' . app_lang('private') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "setor",
                    "name" => "setor",
                    "value" => $model_info->setor,
                    "class" => "form-control",
                    "placeholder" => app_lang('setor')
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group cnpj">
        <div class="row">
            <label for="state_subscription" class=" col-md-3"><?php echo app_lang('state_subscription'); ?></label>
            <div class=" col-md-9">
                <?php
                echo form_input(array(
                    "id" => "state_subscription",
                    "name" => "state_subscription",
                    "value" => $model_info->state_subscription,
                    "class" => "form-control",
                    "placeholder" => app_lang('state_subscription'),
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group cnpj">
        <div class="row">
            <label for="city_subscription" class=" col-md-3"><?php echo app_lang('city_subscription'); ?></label>
            <div class=" col-md-9">
                <?php
                echo form_input(array(
                    "id" => "city_subscription",
                    "name" => "city_subscription",
                    "value" => $model_info->city_subscription,
                    "class" => "form-control",
                    "placeholder" => app_lang('city_subscription'),
                ));
                ?>
            </div>
        </div>
    </div>
<?php } ?>

<div class="form-group">
    <div class="row">
        <label for="limit_date_for_nota_fiscal" class="<?php echo $label_column; ?>"><?php echo app_lang('limit_date_for_nota_fiscal'); ?>
            <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('day_of_month') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
        </label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "limit_date_for_nota_fiscal",
                "name" => "limit_date_for_nota_fiscal",
                "value" => $model_info->limit_date_for_nota_fiscal,
                "class" => "form-control",
                "placeholder" => app_lang('limit_date_for_nota_fiscal'),
                "autocomplete" => "off",
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
        <div class="row">
            <label for="invoice_rule_id" class="<?php echo $label_column; ?>"><?php echo app_lang('invoice_rule'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('invoice_rule_detail') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "invoice_rule_id",
                    "name" => "invoice_rule_id",
                    "value" => $model_info->invoice_rule_id,
                    "class" => "form-control",
                    "placeholder" => app_lang('invoice_rule')
                ));
                ?>
            </div>
        </div>
    </div>
<div class="form-group invoice_rule_date <?=((!$model_info->invoice_rule_date) || $model_info->invoice_rule_date == 0)  ? 'hide' : '' ?>">
    <div class="row">
        <label for="invoice_rule_date" class=" col-md-3"><?php echo app_lang('invoice_rule_date'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "invoice_rule_date",
                "name" => "invoice_rule_date",
                "value" => $model_info->invoice_rule_date,
                "class" => "form-control",
                "placeholder" => app_lang('invoice_rule_date'),
                "autocomplete" => "off"
            ));
            ?>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <label for="lead_interested" class=" col-md-3"><?php echo app_lang('lead_interested'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "lead_interested",
                "name" => "lead_interested",
                "value" => $model_info->lead_interested,
                "class" => "form-control",
                "placeholder" => app_lang('lead_interested'),
            ));
            ?>
        </div>
    </div>
</div>

<?php if ($login_user->is_admin || get_array_value($login_user->permissions, "client") === "all") { ?>
    <div class="form-group">
        <div class="row">
            <label for="created_by" class="<?php echo $label_column; ?>"><?php echo app_lang('owner'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('the_person_who_will_manage_this_client') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "created_by",
                    "name" => "created_by",
                    "value" => $model_info->created_by ? $model_info->created_by : $login_user->id,
                    "class" => "form-control",
                    "placeholder" => app_lang('owner'),
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required")
                ));
                ?>
            </div>
        </div>
    </div>
<?php } ?>


<?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => $label_column, "field_column" => $field_column)); ?> 

<div class="form-group">
    <div class="row">
        <label for="address" class="<?php echo $label_column; ?>"><?php echo app_lang('address'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_textarea(array(
                "id" => "address",
                "name" => "address",
                "value" => $model_info->address ? $model_info->address : "",
                "class" => "form-control",
                "placeholder" => app_lang('address')
            ));
            ?>

        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="city" class="<?php echo $label_column; ?>"><?php echo app_lang('city'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "city",
                "name" => "city",
                "value" => $model_info->city,
                "class" => "form-control",
                "placeholder" => app_lang('city')
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="state" class="<?php echo $label_column; ?>"><?php echo app_lang('state'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "state",
                "name" => "state",
                "value" => $model_info->state,
                "class" => "form-control",
                "placeholder" => app_lang('state')
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="zip" class="<?php echo $label_column; ?>"><?php echo app_lang('zip'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "zip",
                "name" => "zip",
                "value" => $model_info->zip,
                "class" => "form-control",
                "placeholder" => app_lang('zip')
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="country" class="<?php echo $label_column; ?>"><?php echo app_lang('country'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "country",
                "name" => "country",
                "value" => $model_info->country,
                "class" => "form-control",
                "placeholder" => app_lang('country')
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="phone" class="<?php echo $label_column; ?>"><?php echo app_lang('phone'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "phone",
                "name" => "phone",
                "value" => $model_info->phone,
                "class" => "form-control",
                "placeholder" => app_lang('phone')
            ));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <label for="website" class="<?php echo $label_column; ?>"><?php echo app_lang('website'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "website",
                "name" => "website",
                "value" => $model_info->website,
                "class" => "form-control",
                "placeholder" => app_lang('website')
            ));
            ?>
        </div>
    </div>
</div>

<?php if ($login_user->user_type === "staff") { ?>
    <!-- <div class="form-group">
        <div class="row">
            <label for="groups" class="<?php // echo $label_column; ?>"><?php // echo app_lang('client_groups'); ?></label>
            <div class="<?php // echo $field_column; ?>">
                <?php
                // echo form_input(array(
                //     "id" => "group_ids",
                //     "name" => "group_ids",
                //     "value" => $model_info->group_ids,
                //     "class" => "form-control",
                //     "placeholder" => app_lang('client_groups')
                // ));
                ?>
            </div>
        </div>
    </div> -->
<?php } ?>

<?php if ($login_user->is_admin && get_setting("module_invoice")) { ?>
    <div class="form-group">
        <div class="row">
            <label for="currency" class="<?php echo $label_column; ?>"><?php echo app_lang('currency'); ?></label>
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
            <label for="currency_symbol" class="<?php echo $label_column; ?>"><?php echo app_lang('currency_symbol'); ?></label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "currency_symbol",
                    "name" => "currency_symbol",
                    "value" => $model_info->currency_symbol,
                    "class" => "form-control",
                    "placeholder" => app_lang('keep_it_blank_to_use_default') . " (" . get_setting("currency_symbol") . ")"
                ));
                ?>
            </div>
        </div>
    </div>

<?php } ?>

<?php if ($login_user->is_admin && get_setting("module_invoice")) { ?>
    <div class="form-group">
        <div class="row">
            <label for="disable_online_payment" class="<?php echo $label_column; ?> col-xs-8 col-sm-6"><?php echo app_lang('disable_online_payment'); ?>
                <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('disable_online_payment_description') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
            </label>
            <div class="<?php echo $field_column; ?> col-xs-4 col-sm-6">
                <?php
                echo form_checkbox("disable_online_payment", "1", $model_info->disable_online_payment ? true : false, "id='disable_online_payment' class='form-check-input'");
                ?>                       
            </div>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();

<?php if (isset($currency_dropdown)) { ?>
            if ($('#currency').length) {
                $('#currency').select2({data: <?php echo json_encode($currency_dropdown); ?>});
            }
<?php } ?>

<?php if (isset($setor_dropdown)) { ?>
            $("#setor").select2({
                data: <?php echo json_encode($setor_dropdown); ?>
            });
<?php } ?>

<?php if (isset($groups_dropdown)) { ?>
            $("#group_ids").select2({
                multiple: true,
                data: <?php echo json_encode($groups_dropdown); ?>
            });
<?php } ?>

<?php if (isset($invoice_rules_dropdown)) { ?>
            $("#invoice_rule_id").select2({
                data: <?php echo json_encode($invoice_rules_dropdown); ?>
            });
<?php } ?>

<?php if ($login_user->is_admin || get_array_value($login_user->permissions, "client") === "all") { ?>
            $('#created_by').select2({data: <?php echo $team_members_dropdown; ?>});
<?php } ?>

        $("#cnpj").on('change', function() {
            var cnpj = this.value;

            // Remover a máscara do CNPJ (remover pontos, barra e hífen)
            cnpj = cnpj.replace(/[^\d]/g, ''); // Expressão regular para remover tudo que não for dígito

            if (cnpj.length >= 14) {
                // Formatar a URL com o CNPJ digitado
                var url = "https://open.cnpja.com/office/" + cnpj;

                // Fazer a requisição para a API
                fetch(url)
                    .then(response => response.json()) // Parseia a resposta JSON
                    .then(data => {
                        if (data.code !== 401) {
                            // Verificando se a API retornou um nome de empresa válido
                            if (data.company && data.company.name) {
                                // Preencher os campos com os dados retornados
                                $('#custom_field_12').val(data.company.name);  // Preenche o campo com o nome da empresa
                            }
                            if (data.alias) {
                                $('#company_name').val(data.alias.toUpperCase());  // Preenche o campo com o nome da empresa
                            }
                            // Preencher o endereço (se disponível)
                            if (data.address) {
                                var endereco = data.address.street + ", " + data.address.number;
                                $('#address').val(endereco); // Preenche o campo com o endereço da empresa
                                $('#city').val( data.address.city ); // Preenche o campo com o endereço da empresa
                                $('#state').val( data.address.state ); // Preenche o campo com o endereço da empresa
                                $('#zip').val( data.address.zip ); // Preenche o campo com o endereço da empresa
                                $('#country').val( data.address.country.name ); // Preenche o campo com o endereço da empresa
                            } 

                            // Preencher o telefone (se disponível)
                            if (data.phones && data.phones.length > 0) {
                                var telefone = data.phones.map(function(phone) {
                                    return phone.area + ' ' + phone.number;
                                }).join(', ');
                                $('#phone').val(telefone); // Preenche o campo com o telefone
                            }

                             // Verificar se a empresa é do setor público ou privado
                            var natureza = data.company.nature ? data.company.nature.text : '';
                            if (natureza.includes('Público')) {
                                $('#setor').select2("val", "public").trigger('change');
                            } else {
                                $('#setor').select2("val", "private").trigger('change');
                            }

                        } else {
                            console.log('CNPJ não encontrado ou inválido.');
                            // Caso o CNPJ não seja encontrado ou seja inválido, limpar os campos
                        
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar informações do CNPJ:', error);
                    });
            }
        });
       
        $('#invoice_rule_id').on('change', function(){
            
            /* Caso alterado para data específica mostrar input pra seleção da data */
            if(this.value == 2)
            {
                $(".invoice_rule_date").removeClass('hide');
            }
            else{
                $(".invoice_rule_date").addClass('hide');
                $('#invoice_rule_date').attr('value', '');
            }
        })

        setTimeout(() => {
            $("input[name='cnpj']").mask('##.###.###/####-##', { reverse: false });
            $("input[name='matriz_cnpj']").mask('##.###.###/####-##', { reverse: false });
        }, 1000);

        $('.account_type').click(function () {
            var inputValue = $(this).attr("value");
            if (inputValue === "person") {
                $(".cnpj").addClass('hide');
                $(".cnpj").addClass('hide');
                $(".company_name_section").html("<?php echo app_lang("name"); ?>");
                $(".company_name_input_section").attr("placeholder", "<?php echo app_lang("name"); ?>");
            } else {
                $(".cnpj").removeClass('hide');
                $(".company_name_section").html("<?php echo app_lang("company_name"); ?>");
                $(".company_name_input_section").attr("placeholder", "<?php echo app_lang("company_name"); ?>");
            }
        });

    });
</script>