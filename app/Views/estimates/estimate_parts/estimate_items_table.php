<?php

$secondary_color = '#cacfe1';
$color = get_setting("estimate_color");
if (!$color) {
    $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#17365D";
}

$discount_row = '<tr>
                        <td colspan="4" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                    </tr>';

$total_after_discount_row = '<tr>
                                    <td colspan="4" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->estimate_subtotal - $estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                                </tr>';
?>

<!-- <table class="table-responsive" style="width: 100%; color: #444;"> 
    <thead></thead> 
    <tbody>
        <tr style="font-weight: bold; background-color: <?php //echo $color; ?>; color: #fff;  ">
            <?php foreach ($estimate_items as $item) { ?>
                <?php if($item->title != 'Mão de Obra') { ?>
                    <td style="width: 45%; border: 1px solid #fff; padding: 10px;"><?php //echo $item->title; echo ' - '; echo $item->category; ?>
                        <br />
                        <span style="color: #888; font-size: 90%;color: white;"><?php //echo nl2br($item->description ? process_images_from_content($item->description) : ""); ?></span>
                    </td>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <?php foreach ($estimate_items as $item) {
                    if($item->title != 'Mão de Obra') {

                        $source_path = "";
                        $timeline_file_path = get_setting("timeline_file_path");
                        $estimate_files = unserialize($item->files);
                        $estimate_file = "";
                        $options = "";
                        if(!empty($estimate_files))
                        {
                            foreach ($estimate_files as $file) {
                                $source_path = $timeline_file_path . get_array_value($file, "file_name");
                                $file_name = get_array_value($file, "file_name");
        
                                $options = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-minus mr10 float-start"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>';

                                if (is_viewable_image_file($file_name)) {
                                    $thumbnail = get_source_url_of_file($file, $timeline_file_path, "thumbnail");
                                    $estimate_file = "<a href=\"".get_uri("estimates/download_file/" . $file_name)."\" title=\"".app_lang("download")."\"><div class=\"col-md-6 col-sm-6 pr0 saved-file-item-container\"><div style=\"height: 15vh;width: 100px;background-size: contain;background-repeat:no-repeat;background-image: url(". base_url() ."/$source_path)\" class\"edit-image-file mb15\" ></div></div></a>";
                                } else {
                                    $estimate_file = "<div class=\"box saved-file-item-container\"><a title=\"".app_lang("download")."\" href=\"".get_uri("estimates/download_file/" . $file_name)."\"><div class=\"box-content w80p pt5 pb5\">" . $options . remove_file_prefix($file_name) . "</div></a></div>";
                                }
                            }
                        }
                    ?>
                <td style="text-align: center; width: 15%; border: 1px solid #fff;"> <?php //echo $estimate_file; ?></td>
                <?php } ?>
            <?php } ?>
        </tr>
    </tbody>         
</table> -->

<!-- <br/><b>1. ESCOPO DO PROJETO: </b><br/> -->
<?php
    $categories = array();
    $categories_value = array();
    $categories_value_total = array();
    $categories_locacao = array();
    $categories_value_locacao = array();
    $categories_value_total_locacao = array();
    $categories_projeto = array();
    $categories_value_projeto = array();
    $categories_value_total_projeto = array();
    $categories_service = array();
    $categories_value_service = array();
    $categories_value_total_service = array();
    foreach ($estimate_items as $item) 
    {
        if($item->title == 'Projeto' or $item->category == 'Projeto') 
        {
            $categories_projeto[$item->title . ' ' . ($item->description ?? '')][] = $item;
            $categories_value_total_projeto[$item->title . ' ' . $item->description] =  floatval($categories_value_total_projeto[$item->title . ' ' . $item->description] ?? 0) + floatval($item->total);
        }
        elseif($item->title == 'Locação' or $item->category == 'Locação') 
        {
            if(isset($categories_locacao[$item->title . ' ' . $item->description. ' ' . $item->category]))
            {
                $categories_locacao[$item->title . ' ' . $item->description . ' ' . $item->category]->total[] = $item->total;
                $categories_locacao[$item->title . ' ' . $item->description . ' ' . $item->category]->quantity[] = $item->quantity;
            }
            else {
                $categories_locacao[$item->title . ' ' . $item->description . ' ' . $item->category] = $item;
                $categories_locacao[$item->title . ' ' . $item->description . ' ' . $item->category]->total = Array($item->total);
                $categories_locacao[$item->title . ' ' . $item->description . ' ' . $item->category]->quantity = Array($item->quantity);
                $categories_value_locacao[$item->title . ' ' . $item->description . ' ' . $item->category] =  floatval($categories_value_locacao[$item->title . ' ' . $item->description . ' ' . $item->category] ?? 0) + floatval($item->rate);
                $categories_value_total_locacao[$item->title . ' ' . $item->description . ' ' . $item->category] =  floatval($categories_value_total_locacao[$item->title . ' ' . $item->description . ' ' . $item->category] ?? 0) + floatval($item->total);
            }
        }
        elseif(in_array($item->title, ['Mão de Obra', 'Operação', 'Monitoramento', 'Manutenção', 'Rastreamento', 'Instalação']) or  in_array($item->category, ['Mão de Obra', 'Operação', 'Monitoramento', 'Manutenção', 'Rastreamento', 'Instalação'])) 
        {
            $categories_service[$item->title . ' ' . $item->description][] = $item;
            $categories_value_service[$item->title . ' ' . $item->description] =  floatval($categories_value_service[$item->title . ' ' . $item->description] ?? 0) + floatval($item->rate);
            $categories_value_total_service[$item->title . ' ' . $item->description] =  floatval($categories_value_total_service[$item->title . ' ' . $item->description] ?? 0) + floatval($item->total);
        }
        else
        {
            $categories[$item->title][] = $item;
            $categories_value[$item->title] =  floatval($categories_value[$item->title] ?? 0) + floatval($item->rate);
            $categories_value_total[$item->title] =  floatval($categories_value_total[$item->title] ?? 0) + floatval($item->total);
        }
    }
    $contador = 1;
    $contador_categorias = 1;
?>

<?php if(!empty($categories_locacao) OR !empty($categories) OR !empty($categories_service) OR !empty($categories_projeto)) : ?>
    <br/><b>VALORES: </b><br/>

    <?php if(!empty($categories)) : ?>
        <br/><b>OPÇÃO VENDA </b><br/>
        <?php $contador_categorias = 1;?>
        <table>
            <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
                <th style="width: 15%; border-right: 1px solid #eee;"> <?php echo 'ITEM'; ?></th>
                <th style="width: 70%; border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
                <th style="width: 10%; border-right: 1px solid #eee;"><?php echo 'QTD'; ?></th>
                <?php if(array_sum($categories_value_total) != 0): ?>
                    <th style="text-align: center;  width: 15%; border-right: 1px solid #eee;"> <?php echo 'SUBTOTAL'; ?></th>
                <?php endif; ?>
            </tr>
            <?php foreach($categories as $key => $category): ?>
                <?php foreach($category as $k => $v): ?>
                    <tr style="color: <?php echo $color; ?>;  ">
                        <td style="width: 15%; border-right: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
                        <td style="width: 70%; border-right: 1px solid #eee;"><?php echo ($v->title ?? '') . ((!isset($v->category) or $v->category == 'Equipamento') ? '' : (' - ' . $v->category)); ?><br><small><?php echo ($v->description ?? ''); ?></small></td>
                        <td style="width: 70%; border-right: 1px solid #eee;"><?php echo ($v->unit_type == 'qtb') ? 'QTB' : $v->quantity ; ?></td>
                        <?php if(array_sum($categories_value_total) != 0): ?>
                            <td style="text-align: center;  width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency($v->total, 'R$'); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php $contador_categorias++; ?>
            <?php endforeach; ?>
            
            <?php if(array_sum($categories_value_total) != 0): ?>
                <tr style="background-color: <?php echo $secondary_color?>;">
                    <td colspan="3" style="width: 15%; border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
                    <td style="width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency(array_sum($categories_value_total), 'R$'); ?></td>
                </tr>
            <?php endif; ?>
            
            
            <?php
            if ($estimate_total_summary->discount_total && $estimate_total_summary->discount_type == "before_tax") {
                echo $discount_row . $total_after_discount_row;
            }
            ?>  
            <?php if ($estimate_total_summary->tax) { ?>
                <tr>
                    <td colspan="3" style="text-align: right;"><?php echo $estimate_total_summary->tax_name; ?></td>
                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                        <?php echo to_currency($estimate_total_summary->tax, $estimate_total_summary->currency_symbol); ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($estimate_total_summary->tax2) { ?>
                <tr>
                    <td colspan="3" style="text-align: right;"><?php echo $estimate_total_summary->tax_name2; ?></td>
                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                        <?php echo to_currency($estimate_total_summary->tax2, $estimate_total_summary->currency_symbol); ?>
                    </td>
                </tr>
            <?php } ?>
            <?php
            if ($estimate_total_summary->discount_total && $estimate_total_summary->discount_type == "after_tax") {
                echo $discount_row;
            }
            ?> 
        </table><br/>
    <?php endif;?>

    <?php if(!empty($categories_projeto)) : ?>
        <br/><b>OPÇÃO PROJETO</b><br/>

        <?php $contador_categorias = 1;?>
        <table>
            <thead></thead>
            <tbody>
            <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
                <th style="width: 10%; border-right: 1px solid #eee;"> <?php echo 'ITEM'; ?></th>
                <th style="width: 50%; border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
                    <th style="width: 50%; border-right: 1px solid #eee;"><?php echo 'QTD'; ?></th>
            </tr>
            <?php foreach($categories_projeto as $key => $category): ?>
                <tr style="color: <?php echo $color; ?>;  ">
                    <td style="width: 10%; border-right: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
                    <td style="width: 50%; border-right: 1px solid #eee;"><?php echo ($category[0]->title ?? ''); ?><br><small><?php echo ($category[0]->description ?? ''); ?></small></td>
                     <td style="width: 10%; border-right: 1px solid #eee;"><?php echo (strtolower($category[0]->unit_type) == 'qtb') ? 'QTB' : array_sum(array_column($category,'quantity')); ?></td>
                </tr>
                <?php $contador_categorias++; ?>
            <?php endforeach; ?>
            <?php if(array_sum($categories_value_total_projeto) != 0): ?>
                <tr style="background-color: <?php echo $secondary_color?>;">
                    <td colspan="2" style="width: 15%; border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
                    <td style="width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency(array_sum($categories_value_total_projeto), 'R$'); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table><br/>
    <?php endif;?>
    <?php if(!empty($categories_locacao)) : ?>
        <br/><b>OPÇÃO LOCAÇÃO DE EQUIPAMENTOS COM MANUTENÇÃO INCLUSA</b><br/>

        <?php $contador_categorias = 1; $column = Array(); ?>
        <table>
            <thead></thead>
            <tbody>
            <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
                <th style="width: 10%; border-right: 1px solid #eee;"> <?php echo 'ITEM'; ?></th>
                <th style="width: 50%; border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
                <?php foreach($categories_locacao as $key => $category): ?>
                    <?php foreach($category->quantity as $k => $v): ?>
                        <?php if(!in_array($v, $column)): $column[] = $v; endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php foreach($column as $k => $v): ?>
                    <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo $v.' MESES'; ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach($categories_locacao as $key => $category): ?>
                    <tr style="color: <?php echo $color; ?>;  ">
                        <td style="width: 10%; border-right: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
                        <td style="width: 50%; border-right: 1px solid #eee;"><?php echo ($category->title ?? '');; ?><br><small><?php echo ($category->description ?? ''); ?></small></td>
                        <!-- <pre> -->
                        <?php foreach($column as $k => $v): ?>
                            <td style="width: 10%; border-right: 1px solid #eee;"><?= isset($category->total[$k]) ? to_currency($category->total[$k], 'R$') : ''?></td>
                        <?php endforeach; ?>
                    <!-- </pre> -->
                    </tr>
                    <?php $contador_categorias++; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        Obs.: Os materiais e mão de obra de instalação, não entram na locação de equipamentos.<br/><br/>
    <?php endif;?>
    
    <?php if(!empty($categories_service)) : ?>
        <br/><b>OPÇÃO SERVIÇOS/MÃO DE OBRA</b><br/>

        <?php $contador_categorias = 1;?>
        <table>
            <thead></thead>
            <tbody>
            <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
                <th style="width: 10%; border-right: 1px solid #eee;"> <?php echo 'ITEM'; ?></th>
                <th style="width: 50%; border-right: 1px solid #eee;"><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></th>
                <!-- <th style="width: 50%; border-right: 1px solid #eee;"><?php // echo 'QTD'; ?></th> -->
                <?php if(array_sum($categories_value_total_service) != 0): ?>
                    <th style="text-align: center;  width: 15%; border-right: 1px solid #eee;"> <?php echo 'SUBTOTAL'; ?></th>
                <?php endif; ?>
            </tr>
            <?php foreach($categories_service as $key => $category): ?>
                <?php foreach($category as $k => $v): ?>
                    <tr style="color: <?php echo $color; ?>;  ">
                        <td style="width: 10%; border-right: 1px solid #eee;"> <?php echo $contador_categorias; ?> </td>
                        <td style="width: 50%; border-right: 1px solid #eee;"><?php echo $v->title; ?><br><small><?php echo ($v->description ?? ''); ?></small></td>
                        <!-- <td style="width: 70%; border-right: 1px solid #eee;"><?php //echo (strtolower($v->unit_type) == 'qtb') ? 'QTB' : array_sum(array_column($category,'quantity')); ?></td> -->
                        <?php if(array_sum($categories_value_total_service) != 0): ?>
                            <td style="text-align: center;  width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency($v->total, 'R$'); ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php $contador_categorias++; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if(array_sum($categories_value_total_service) != 0): ?>
                <tr style="background-color: <?php echo $secondary_color?>;">
                    <td colspan="2" style="width: 15%; border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
                    <td style="width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency(array_sum($categories_value_total_service), 'R$'); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table><br/>
    <?php endif;?>
    <?php if(array_sum($categories_value_total_service) == 0 and array_sum($categories_value_total_projeto) == 0 and array_sum($categories_value_total) == 0): ?>
        <table>
            <tbody>
                <tr style="background-color: <?php echo $secondary_color?>;">
                    <td  style="width: 15%; border-right: 1px solid #eee;"> <?php echo 'VALOR TOTAL'; ?></td>
                    <td style="width: 15%; border-right: 1px solid #eee;"> <?php echo to_currency(floatval($estimate_info->custom_fields[1]->value), 'R$'); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif;?>
<?php endif;?>