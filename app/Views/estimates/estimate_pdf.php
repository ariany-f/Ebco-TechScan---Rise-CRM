<div style=" margin: auto;">
    <?php
    $color = get_setting("estimate_color");
    if (!$color) {
        $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#17365D";
    }
    $style = get_setting("invoice_style");
    ?>
    <?php
    $data = array(
        "client_info" => $client_info,
        "color" => $color,
        "estimate_info" => $estimate_info
    );
    
    if ($style === "style_3") {
        echo view('estimates/estimate_parts/header_style_3.php', $data);
    } else if ($style === "style_2") {
        echo view('estimates/estimate_parts/header_style_2.php', $data);
    } else {
        echo view('estimates/estimate_parts/header_style_1.php', $data);
    }

    $discount_row = '<tr>
                        <td colspan="3" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                    </tr>';

    $total_after_discount_row = '<tr>
                                    <td colspan="3" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->estimate_subtotal - $estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                                </tr>';
    ?>
</div>
<br /><br />
<b style="font-family:Barlow Bold, sans-serif;color: #17365D;font-size: 18px;">1. ESCOPO DO PROJETO</b>
<br /><br />

<?php
    $secondary_color = '#cacfe1';
    $color = get_setting("estimate_color");
    $categories = array();
    $categories_value = array();
    $categories_value_total = array();
    $categories_locacao = array();
    $categories_value_locacao = array();
    $categories_value_total_locacao = array();
    foreach ($estimate_items as $item) {
        if($item->title != 'Locação')
        {
            $categories[$item->title][] = $item;
            $categories_value[$item->title] =  floatval($categories_value[$item->title] ?? 0) + floatval($item->rate);
            $categories_value_total[$item->title] =  floatval($categories_value_total[$item->title] ?? 0) + floatval($item->total);
        }
        else
        {
            $categories_locacao[$item->title][] = $item;
            $categories_value_locacao[$item->title] =  floatval($categories_value[$item->title] ?? 0) + floatval($item->rate);
            $categories_value_total_locacao[$item->title] =  floatval($categories_value_total[$item->title] ?? 0) + floatval($item->total);

        }
    }
    $contador = 1;
    $contador_categorias = 1;
?>

<table>
    <tr valign="center" style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;font-family:Barlow Bold, sans-serif;line-height: 50px;text-align: center;">
        <th class="text-center" colspan="3">DESCRIÇÃO DO PROJETO</th>
    </tr>
    <?php foreach($categories as $key => $category){ ?>
        <tr valign="center" style="line-height: 40px;font-family:Barlow Bold, sans-serif; background-color: <?php echo $secondary_color; ?>; color: <?php echo $color; ?>;  ">
            <th style="width: 15%; border: 1px solid #eee;text-align:center;font-family:Barlow Bold, sans-serif; "> <?php echo strtoupper(app_lang("itens")); ?> </th>
            <th style="width: 70%; border: 1px solid #eee;text-align:center;"><?php echo strtoupper($key); echo ' (' ; echo $contador_categorias; echo ')'; ?></th>
            <th style="font-size: 14px;text-align: center;  width: 15%;border: 1px solid #eee;text-align:center;"> <?php echo strtoupper(app_lang("quantity")); ?></th>
        </tr>
            <?php foreach ($estimate_items as $chave => $item) { ?>
                <?php if($item->title == $key){ ?>
                <tr style="line-height: 40px;border: 1px solid #eee;">
                    <td style="width: 15%;text-align:center;border: 1px solid #eee;"> <?php echo $contador; ?></td>
                    <td style="width: 70%;text-align:center;border: 1px solid #eee;"><?php echo $item->category; ?></td>
                    <td style="width: 15%;text-align:center;border: 1px solid #eee;"> <?php echo ($item->category != 'Mão de Obra') ? $item->quantity : '' ?></td>
                    <?php $contador++; ?>
                </tr>
                <?php } ?>
            <?php } ?>
        <?php $contador_categorias++; ?>
    <?php } ?>
</table>

<b style="font-family:Barlow Bold, sans-serif;color: #17365D;font-size: 18px;">2. VALORES</b>
<br />

<br/><b  style="font-family:Barlow Bold, sans-serif;color: #17365D;font-size: 15px;">OPÇÃO VENDA </b><br/><br/>

<?php $contador_categorias = 1;?>
<table>
    <tr valign="center" style="font-family:Barlow Bold, sans-serif;background-color: <?php echo $color; ?>; color: #fff;font-size: 14px;text-align: center;line-height: 30px;">
        <th style="width: 25%;border-right: 1px solid #eee;"> <?php echo 'ITEM DO ESCOPO'; ?></th>
        <th style="width: 25%;border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
        <th style="width: 10%;border-right: 1px solid #eee;"><?php echo 'QTD'; ?></th>
        <th style="width: 20%;text-align: center;border-right: 1px solid #eee;"> <?php echo 'VALOR VENDA'; ?></th>
        <th style="width: 20%;text-align: center;border-right: 1px solid #eee;"> <?php echo 'SUBTOTAL'; ?></th>
    </tr>
    <?php foreach($categories as $key => $category): ?>
        <tr valign="center" style="color: <?php echo $color; ?>;text-align: center;line-height: 40px;border: 1px solid #eee;">
            <td style="border: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
            <td style="border: 1px solid #eee;"><?php echo $key; ?></td>
            <td style="border: 1px solid #eee;"><?php echo array_sum(array_column($category,'quantity')); ?></td>
            <td style="text-align: center;border: 1px solid #eee;"> <?php echo to_currency($categories_value[$key], 'R$'); ?></td>
            <td style="text-align: center;border: 1px solid #eee;"> <?php echo to_currency($categories_value_total[$key], 'R$'); ?></td>
        </tr>
        <?php $contador_categorias++; ?>
    <?php endforeach; ?>
    <tr style="background-color: <?php echo $secondary_color?>;text-align: center;line-height: 40px;">
        <td colspan="4" style="width: 70%;border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
        <td style="width: 30%;border-right: 1px solid #eee;"> <?php echo to_currency(array_sum($categories_value_total), 'R$'); ?></td>
    </tr>
    
    
    <?php
    if ($estimate_total_summary->discount_total && $estimate_total_summary->discount_type == "before_tax") {
        echo $discount_row . $total_after_discount_row;
    }
    ?>  
    <?php if ($estimate_total_summary->tax) { ?>
        <tr style="text-align: center;line-height: 40px;">
            <td colspan="4" style="width: 70%;text-align: right;"><?php echo $estimate_total_summary->tax_name; ?></td>
            <td style="width: 30%;text-align: right; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($estimate_total_summary->tax, $estimate_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($estimate_total_summary->tax2) { ?>
        <tr style="text-align: center;line-height: 40px;">
            <td colspan="4" style="width: 70%;text-align: right;"><?php echo $estimate_total_summary->tax_name2; ?></td>
            <td style="width: 30%;text-align: right; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($estimate_total_summary->tax2, $estimate_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php
    if ($estimate_total_summary->discount_total && $estimate_total_summary->discount_type == "after_tax") {
        echo $discount_row;
    }
    ?> 
</table>

<br/><br/><b  style="font-family:Barlow Bold, sans-serif;color: #17365D;font-size: 15px;">OPÇÃO LOCAÇÃO DE EQUIPAMENTOS COM MANUTENÇÃO INCLUSA</b><br/><br/>

<?php $contador_categorias = 1;?>
<table>
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;text-align: center;font-size: 14px;line-height: 30px;">
        <th style="width: 25%;border-right: 1px solid #eee;"> <?php echo 'ITEM DO ESCOPO'; ?></th>
        <th style="width: 25%;border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
        <th style="width: 12.5%;border-right: 1px solid #eee;"><?php echo '12 MESES'; ?></th>
        <th style="width: 12.5%;border-right: 1px solid #eee;"> <?php echo '24 MESES'; ?></th>
        <th style="width: 12.5%;border-right: 1px solid #eee;"> <?php echo '36 MESES'; ?></th>
        <th style="width: 12.5%;border-right: 1px solid #eee;"> <?php echo '48 MESES'; ?></th>
    </tr>
    <?php foreach($categories_locacao as $key => $category): ?>
        <tr style="color: <?php echo $color; ?>;text-align: center;line-height: 40px;border: 1px solid #eee;">
            <td style="border-right: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
            <td style="border-right: 1px solid #eee;"><?php echo $key; ?></td>
            <td style="border-right: 1px solid #eee;font-size: 14px;"><?php echo array_sum(array_column($category,'quantity')) == 12 ? to_currency($categories_value_total_locacao[$key], 'R$') : '' ; ?></td>
            <td style="border-right: 1px solid #eee;font-size: 14px;"><?php echo array_sum(array_column($category,'quantity')) == 24 ? to_currency($categories_value_total_locacao[$key], 'R$') : '' ; ?></td>
            <td style="border-right: 1px solid #eee;font-size: 14px;"><?php echo array_sum(array_column($category,'quantity')) == 36 ? to_currency($categories_value_total_locacao[$key], 'R$') : '' ; ?></td>
            <td style="border-right: 1px solid #eee;font-size: 14px;"><?php echo array_sum(array_column($category,'quantity')) == 48 ? to_currency($categories_value_total_locacao[$key], 'R$') : '' ; ?></td>
        </tr>
        <?php $contador_categorias++; ?>
    <?php endforeach; ?>
    <tr style="background-color: <?php echo $secondary_color?>;text-align: center;line-height: 40px;">
        <td colspan="5" style="width: 70%;border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
        <td style="width: 30%;border-right: 1px solid #eee;"> <?php echo to_currency(array_sum($categories_value_total_locacao), 'R$'); ?></td>
    </tr>
</table>

<table>
    <tr>
        <td style="color: #17365D;font-size: 15px;">
            <br/>
            <br/>
            Obs.: Os materiais e mão de obra de instalação, não entram na locação de equipamentos.
            <br/>
        </td>
    </tr>
    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">3. CONDIÇÕES DE PAGAMENTO:</b>
            <?php if ($estimate_info->note) { ?>
                <div style="border-top: 2px solid #f2f2f2; color: #17365D; font-size: 15px; padding:0 0 20px 0;"><br /><?php echo nl2br(process_images_from_content($estimate_info->note)); ?></div>
            <?php } else { ?><!-- use table to avoid extra spaces -->
                <br /><br /><table class="invoice-pdf-hidden-table" style="border-top: 2px solid #f2f2f2; margin: 0; padding: 0; display: block; width: 100%; height: 10px;"></table>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">4. VALIDADE DA PROPOSTA:</b>
            <p style="font-size: 15px;">15 dias da data de emissão da proposta</p>
        </td>
    </tr>

    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">5. PRAZOS:</b>
            <p style="font-size: 15px;">Entrega – 30 dias da data de aprovação</p>
            <p style="font-size: 15px;">Execução – 20 dias úteis</p>
        </td>
    </tr>

    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">INFORMAÇÕES IMPORTANTES</b>
            <ul style="font-size: 15px;">
                <li>O faturamento será realizado como serviço.</li>
                <li>O trabalho será executado de segunda à sexta em horário comercial, havendo a necessidade de trabalhos excepcionais a TECHSCAN IMPORTADORA E SERVICOS LTDA deverá ser consultada e poderá gerar adicional de cobrança a esta proposta;</li>
                <li>A TECHSCAN IMPORTADORA E SERVICOS LTDA não se responsabilizará por obras civis que não estejam previstas nesta proposta;</li>
                <li>A plataforma elevatória ou empilhadeira com gaiola serão fornecidas pelo cliente, caso necessário para realização de trabalhos em altura;</li>
                <li>Não está incluso fusão de fibra óptica se necessário.</li>
                <li>Todos os impostos estão inclusos nos valores apresentados.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">PRAZO DE ENTREGA DOS EQUIPAMENTOS</b>
            <p style="font-size: 15px;">A combinar</p>
        </td>
    </tr>
    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">CONDIÇÕES COMERCIAIS</b>
            
            <?php if ($estimate_info->note) { ?>
                <div style="border-top: 2px solid #f2f2f2; color: #17365D;font-size: 15px; padding:0 0 20px 0;"><br /><?php echo nl2br(process_images_from_content($estimate_info->note)); ?></div>
            <?php } else { ?><!-- use table to avoid extra spaces -->
                <br /><br /><table class="invoice-pdf-hidden-table" style="border-top: 2px solid #f2f2f2; margin: 0; padding: 0; display: block; width: 100%; height: 10px;"></table>
            <?php } ?>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="text-align: justify;color: #17365D;font-size: 15px;">
            Registramos que essa é a forma padrão de contratação de nossa empresa. Todavia, estamos abertos a avaliar e alterar a forma de pagamento para modelo que melhor atenda às necessidades do cliente.<br/>
        </td>
    </tr>


    <tr>
        <td colspan="3"style="color: #17365D;font-size: 18px;">
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;margin-left:">VALIDADE DA PROPOSTA</b>
            <p><?php echo format_to_date($estimate_info->estimate_date, false) ?></p>
        </td>
    </tr>
    <?php 
        if ($estimate_info->company_id) {
            $options = array("id" => $estimate_info->company_id);
        }

        $options["deleted"] = 0;

        $Company_model = model('App\Models\Company_model');
        $company_info = $Company_model->get_one_where($options);

        //show default company when any specific company isn't exists
        if ($estimate_info->company_id && !$company_info->id) {
            $options = array("is_default" => true);
            $company_info = $Company_model->get_one_where($options);
        }
    ?>
    <tr>
        <td colspan="3" style="text-align: justify;color: #17365D;font-size: 15px;">
            <br/>
            <br/>
            A <b style="font-family:Barlow Bold, sans-serif;margin-left:"><?php echo $company_info->name ?></b> está à disposição para esclarecer quaisquer dúvidas.
        </td>
    </tr>
</table>
<span style="color:#444; line-height: 14px;">
    <?php echo get_setting("estimate_footer"); ?>
</span>

