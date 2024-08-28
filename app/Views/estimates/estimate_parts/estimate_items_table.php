<?php

$secondary_color = '#cacfe1';
$color = get_setting("estimate_color");
if (!$color) {
    $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#17365D";
}

$discount_row = '<tr>
                        <td colspan="4" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                    </tr>';

$total_after_discount_row = '<tr>
                                    <td colspan="4" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right;  border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($estimate_total_summary->estimate_subtotal - $estimate_total_summary->discount_total, $estimate_total_summary->currency_symbol) . '</td>
                                </tr>';

$regex = "((https?|ftp)\:\/\/)?"; // SCHEME 
$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass 
$regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP 
$regex .= "(\:[0-9]{2,5})?"; // Port 
$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path 
$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query 
$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 

?>

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
    $categories_seabox = array();
    foreach ($estimate_items as $item) 
    {
        if($item->title == 'Seabox' or $item->category == 'Seabox') 
        {
            $categories_seabox[$item->title . ' ' . ($item->description ?? '')][] = $item;
        }
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
        elseif(in_array($item->title, ['Mão de Obra', 'Operação', 'Monitoramento', 'Manutenção', 'Rastreamento', 'Instalação', 'Serviço']) or  in_array($item->category, ['Mão de Obra', 'Operação', 'Monitoramento', 'Manutenção', 'Rastreamento', 'Instalação', 'Serviço'])) 
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

<?php if((!empty($categories_locacao) OR !empty($categories) OR !empty($categories_service) OR !empty($categories_projeto)) AND empty($categories_seabox)) : ?>
   
    <table style="width:100%;min-width: 100%;">
        <tr class="subtitle">
            <td>
                <b>VALORES</b>
            </td>
        </tr>
        <tr>
            <td style="padding:0;">
                <?php if(!empty($categories)) : ?>
                    <?php $contador_categorias = 1;?>
                    <table class="my-table items">
                        <tr class="table-header">
                            <td>
                                <?php echo 'Item (Venda)'; ?>
                            </td>
                            <td>
                                <?php echo 'Descrição Conforme Escopo'; ?>
                            </td>
                            <td>
                                <?php echo 'Qtd'; ?>
                            </td>
                            <?php if(array_sum($categories_value_total) != 0): ?>
                                <td>
                                    <?php echo 'Subtotal'; ?>
                                </td>
                            <?php else: ?>
                                <td>
                                    <?php echo '' ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php foreach($categories as $key => $category): ?>
                            <?php foreach($category as $k => $v): ?>
                                <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                                    <td>
                                        <?php echo $contador_categorias . '.'; ?>
                                    </td>
                                    <td style="padding-left: 15px!important; text-align: left;">
                                        <?php echo ($v->title ?? '') . ((!isset($v->category) or $v->category == 'Equipamento') ? '' : (' - ' . $v->category)); ?>
                                        <br/>
                                        <?php if(preg_match("/^$regex$/i", $v->description)) : ?>
                                            <a style="text-decoration: underline;font-size:16px;margin-top: 10px;margin-bottom: 5px;" href="<?php echo ($v->description ?? ''); ?>">Acessar produto</a>
                                        <?php else:?>
                                            <small><?php echo ($v->description ?? ''); ?></small>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php echo ($v->unit_type == 'qtb') ? 'QTB' : $v->quantity ; ?>
                                    </td>
                                    <?php if(array_sum($categories_value_total) != 0): ?>
                                        <td>
                                            <?php echo ($v->total != 0) ? to_currency($v->total, 'R$') : ''; ?>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <?php echo '' ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php $contador_categorias++; ?>
                        <?php endforeach; ?>
                        
                        <?php if(array_sum($categories_value_total) != 0): ?>
                            <tr class="table-sum">
                                <td colspan="3">
                                    <?php echo 'Valor Total'; ?>
                                </td>
                                <td>
                                    <?php echo to_currency(array_sum($categories_value_total), 'R$'); ?>
                                </td>
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
                                <td style="text-align: right;border: 1px solid #fff; background-color: #f4f4f4;">
                                    <?php echo to_currency($estimate_total_summary->tax, $estimate_total_summary->currency_symbol); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($estimate_total_summary->tax2) { ?>
                            <tr>
                                <td colspan="3" style="text-align: right;"><?php echo $estimate_total_summary->tax_name2; ?></td>
                                <td style="text-align: right; border: 1px solid #fff; background-color: #f4f4f4;">
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
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td style="padding:0;">
                <?php if(!empty($categories_projeto)) : ?>
                    <?php $contador_categorias = 1;?>
                    <table class="my-table items">
                        <tr style="font-weight: bold;line-height: 2rem; word-spacing: .5rem; background-color: <?php echo $color; ?>; color: #fff;  ">
                            <td><?php echo 'Item (Projeto)'; ?></td>
                            <td><?php echo 'Descrição Conforme Escopo'; ?></td>
                            <td><?php echo 'Qtd'; ?></td>
                        </tr>
                        <?php foreach($categories_projeto as $key => $category): ?>
                            <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                                <td><?php echo $contador_categorias . '.'; ?> </td>
                                <td style="padding-left: 15px !important; text-align: left;">
                                    <?php echo ($category[0]->title ?? ''); ?>
                                    <?php if(preg_match("/^$regex$/i", $category[0]->description)) : ?>
                                        <a style="text-decoration: underline;font-size:16px;margin-top: 10px;margin-bottom: 5px;" href="<?php echo ($category[0]->description ?? ''); ?>">Acessar produto</a>
                                    <?php else:?>
                                        <small><?php echo ($category[0]->description ?? ''); ?></small>
                                    <?php endif;?>
                                </td>
                                <td><?php echo (strtolower($category[0]->unit_type) == 'qtb') ? 'QTB' : array_sum(array_column($category,'quantity')); ?></td>
                            </tr>
                            <?php $contador_categorias++; ?>
                        <?php endforeach; ?>
                        <?php if(array_sum($categories_value_total_projeto) != 0): ?>
                            <tr class="table-sum">
                                <td colspan="2"> <?php echo 'Valor Total'; ?></td>
                                <td> <?php echo to_currency(array_sum($categories_value_total_projeto), 'R$'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td style="padding:0;">
                <?php if(!empty($categories_locacao)) : ?>
                    <?php $contador_categorias = 1; $column = Array(); ?>
                    <table class="my-table items">
                        <tr class="table-header">
                            <td> <?php echo 'Item (Locação)'; ?></td>
                            <td><?php echo 'Descrição Conforme Escopo'; ?></td>
                            <?php foreach($categories_locacao as $key => $category): ?>
                                <?php foreach($category->quantity as $k => $v): ?>
                                    <?php if(!in_array($v, $column)): $column[] = $v; endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php foreach($column as $k => $v): ?>
                                <td style="text-align: center;  border-right: 1px solid #eee;"> <?php echo $v.' MESES'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach($categories_locacao as $key => $category): ?>
                                <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>; ">
                                    <td> <?php echo $contador_categorias . '.'; ?> </td>
                                    <td style="padding-left: 15px !important; text-align: left;">
                                        <?php echo ($category->title ?? '');; ?>
                                        <br>
                                        <?php if(preg_match("/^$regex$/i", $category->description)) : ?>
                                            <a style="text-decoration: underline;font-size:16px;margin-top: 10px;margin-bottom: 5px;" href="<?php echo ($category->description ?? ''); ?>">Acessar produto</a>
                                        <?php else:?>
                                            <small><?php echo ($category->description ?? ''); ?></small>
                                        <?php endif;?>
                                    </td>
                                    <!-- <pre> -->
                                    <?php foreach($column as $k => $v): ?>
                                        <td><?= isset($category->total[$k]) ? to_currency($category->total[$k], 'R$') : ''?></td>
                                    <?php endforeach; ?>
                                <!-- </pre> -->
                                </tr>
                                <?php $contador_categorias++; ?>
                        <?php endforeach; ?>
                    </table>
                    <table class="">
                        <tr class="content">
                            <td style="padding: .5rem 0!important;">
                                <p>Obs.: Os materiais e mão de obra de instalação, não entram na locação de equipamentos.</p>
                            </td>
                        </tr>
                    </table>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td style="padding:0;">
                <?php if(!empty($categories_service)) : ?>
                    <?php $contador_categorias = 1;?>
                    <table class="my-table items">
                        <tr class="table-header">
                            <td><?php echo 'Item (SERVIÇO)'; ?></td>
                            <td><?php echo 'DESCRIÇÃO CONFORME ESCOPO'; ?></td>
                            <!-- <th style="width: 50%; border-right: 1px solid #eee;"><?php // echo 'QTD'; ?></th> -->
                            <?php if(array_sum($categories_value_total_service) != 0): ?>
                                <td style="text-align: center;  border-right: 1px solid #eee;"> <?php echo 'SUBTOTAL'; ?></td>
                            <?php else: ?>
                                <td style="text-align: center;  border-right: 1px solid #eee;"> <?php echo '' ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php foreach($categories_service as $key => $category): ?>
                            <?php foreach($category as $k => $v): ?>
                                <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                                    <td> <?php echo $contador_categorias; ?> </td>
                                    <td style="padding-left: 15px !important; text-align: left;"><?php echo $v->title; ?>
                                        <br>
                                        <?php if(preg_match("/^$regex$/i", $v->description)) : ?>
                                            <a style="text-decoration: underline;font-size:16px;margin-top: 10px;margin-bottom: 5px;" href="<?php echo ($v->description ?? ''); ?>">Acessar produto</a>
                                        <?php else:?>
                                            <small><?php echo ($v->description ?? ''); ?></small>
                                        <?php endif;?>
                                    </td>
                                    <!-- <td style="width: 70%; border-right: 1px solid #eee;"><?php //echo (strtolower($v->unit_type) == 'qtb') ? 'QTB' : array_sum(array_column($category,'quantity')); ?></td> -->
                                    <?php if(array_sum($categories_value_total_service) != 0): ?>
                                        <td style="text-align: center;  border-right: 1px solid #eee;"> <?php echo ($v->total != 0) ? to_currency($v->total, 'R$') : ''; ?></td>
                                    <?php else: ?>
                                        <td style="text-align: center;  border-right: 1px solid #eee;"> <?php echo '' ?></td>
                                    <?php endif; ?>
                                </tr>
                                <?php $contador_categorias++; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <?php if(array_sum($categories_value_total_service) != 0): ?>
                            <tr class="table-sum">
                                <td colspan="2"><?php echo 'Valor Total'; ?></td>
                                <td><?php echo to_currency(array_sum($categories_value_total_service), 'R$'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php endif;?>
            </td>
        </tr>
    </table>
    <?php if(array_sum($categories_value_total_service) == 0 and array_sum($categories_value_total_projeto) == 0 and array_sum($categories_value_total) == 0): ?>
        <table class="my-table items">
            <tr class="table-sum">
                <td><?php echo 'Valor Total'; ?></td>
                <td><?php echo to_currency(floatval($estimate_info->custom_fields[1]->value), 'R$'); ?></td>
            </tr>
        </table>
    <?php endif;?>
<?php endif;?>

<?php if(!empty($categories_seabox)): ?>
<?php foreach($categories_seabox as $seabox): ?>
    <?php foreach($seabox as $v): ?>
        <?php if($v->title == 'Segregação de Containers'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de Segregação de Containers.</p>

                <br/>
                <b>VISTORIA DE SEGREGAÇÃO</b>
                <br/>
                <ol style="list-style-type: lower-alpha;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>SELEÇÃO DE “CONTAINERS” para transporte de cargas.</li>
                    <li>CRITÉRIO de vistoria a ser definido posteriormente pelo CLIENTE;</li>
                    <li>CLIENTE DEVE INFORMAR o número da reserva, terminal de retirada, tipo do “container”, quantidade, horário de agendamento da retirada, armador, exportador e quaisquer particularidades;</li>
                    <li>
                        REGISTRO FOTOGRÁFICO DAS UNIDADES selecionadas conforme abaixo:
                        <ul style="list-style-type: circle;padding-left: 1rem;">
                            <li>Foto 1: portas fechadas – visualização da numeração e dados da unidade</li>
                            <li>Foto 2: portas abertas – condição interna da unidade</li>
                        </ul>
                    </li>
                    <li>IDENTIFICAÇÃO DO “CONTAINER” SELECIONADO através de um adesivo de “selecionado”, conforme ANEXO I;</li>
                    <li>“CONTAINERS” COM VISTORIA DE PÓS-REPARO (2ª VISTORIA) serão identificados através de um selo de “pós-reparo OK”, conforme ANEXO II (se modalidade for contratada);</li>
                    <li>
                        Essa modalidade de vistoria exige a necessidade de luz natural para realização do teste de luz e para verificação cuidadosa de todos os componentes do “container”, sendo assim, nosso horário para realização das vistorias será das 7:30 até as 17:00hrs (de Segunda a Sexta feira) e das 7:30 até as 11:30hrs aos Sábados;
                        <ul style="list-style-type: circle;padding-left: 1rem;">
                            <li>STANDBY - Caso seja necessário a presença de nosso vistoriador a partir dos horários acima para aguardar chegada da transportadora ou aguardar o reparo do “container”, será cobrada uma tarifa de (STANDBY).</li>
                            <li>Para vistorias realizadas após as 18:00, mediante solicitação do cliente, não nos responsabilizamos por defeitos que possam ocorrer devido falta de luz natural.</li>
                        </ul>
                    </li>
                    <li>
                        PEDIDOS DE VISTORIA devem ser enviados para Seabox, com antecedência;
                        <ul style="list-style-type: circle;padding-left: 1rem;">
                            <li>Para vistorias no período da tarde, Seabox deve ser informada até as 10:00h da manhã do mesmo dia;</li>
                            <li>Para vistorias no período da manhã, Seabox deve ser informada até as 17:00h do dia útil anterior;</li>
                        </ul>
                    </li>
                    <li>NA NECESSIDADE DE INCLUSÃO NA PROPOSTA, de itens que demandam tempo adicional de trabalho de nosso Administrativo, vistorias aos Domingos e feriados ou compra de material/equipamento para execução da vistoria, uma nova proposta incluindo estes serviços deverá ser enviada para o cliente;</li>
                </ol>
                <br/>
                <b>VISTORIA DE PÓS-REPARO (2ª VISTORIA)</b>
                <br/>
                <p style="color: grey">Poderemos efetuar uma vistoria de pós-reparo (ou 2ª vistoria) nos “containers” onde reparos de “upgrade” foram solicitados durante nosso processo de seleção. O objetivo desta vistoria é o de assegurar que todos estes reparos solicitados pela SEABOX foram (na realidade) efetuados pela oficina e que estão dentro das normas internacionais de reparos.
                Este procedimento dará ao cliente a certeza de que o “container” será retirado pelo transporte dentro dos padrões necessários.  
                Nosso horário limite para atendimento desta modalidade, conforme item “g” acima.</p>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            SEGREGAÇÃO
                        </td>
                        <td>
                            R$ 50,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            SEGREGAÇÃO + PÓS REPARO
                        </td>
                        <td>
                            R$ 70,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            SEGREGAÇÃO - Após as 19hrs
                        </td>
                        <td>
                            R$ 65,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            SEGREGAÇÃO + PÓS REPARO - Após as 19hrs
                        </td>
                        <td>
                            R$ 85,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            STANDBY
                        </td>
                        <td>
                            R$ 60,00/hora
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
         <?php endif; ?>
        <?php if($v->title == 'Vistoria de ON-HIRE e REVALIDAÇÃO DA PLACA DE CSC'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de ON-HIRE (direcionado à aquisição de “containers”) E REVALIDAÇÃO DA PLACA DE CSC.</p>

                <br/>
                <b>ON-HIRE</b>
                <br/>
                <p>A tarifa proposta a seguir contempla os seguintes itens:</p>
                <br/>
                <ol style="list-style-type: lower-alpha;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Vistoria da estrutura em “containers” - (critério CW/ALIMENTO)</li>
                    <li>
                        Será informado em relatório próprio da SEABOX as seguintes informações:
                        <ul style="list-style-type: circle;padding-left: 1rem;">
                            <li>Avarias presentes na unidade (externas e internas)</li>
                            <li>Se o “container” está apto ao transporte de carga (não estando, será rejeitado a menos se instruído de forma diferente)</li>
                            <li>Se o “container” atende o padrão contratado</li>
                        </ul>
                    </li>
                    <li>Registro fotográfico das unidades selecionadas</li>
                    <li>Emissão de relatório de ON-HIRE das unidades selecionadas</li>
                    <li>Envio diário de resultados das vistorias</li>
                </ol>
                <br/>
                <b>REVALIDAÇÃO PLACA CSC:</b>
                <br/>
                <p>A tarifa proposta a seguir contempla os seguintes itens:</p>
                <br/>
                <ol style="list-style-type: lower-alpha;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Registro fotográfico das unidades inspecionadas.</li>
                    <li>Emissão de certificado de revalidação da placa de CSC das unidades inspecionadas e aprovadas.</li>
                    <li>Emissão de certificado (em inglês) de revalidação da placa de CSC das unidades inspecionadas e aprovadas;</li>
                    <li>Validade do certificado - 1 (um) ano;</li>
                    <li>Cliente deve informar nome da empresa que será mencionada no certificado.</li>
                    <li>Os pedidos deverão ser recebidos com antecedência, para agendamento das vistorias.</li>
                </ol>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            On Hire
                        </td>
                        <td>
                            R$ 200,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            On Hire + Revalidação da Placa CSC
                        </td>
                        <td>
                            R$ 350,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            Homem Hora
                        </td>
                        <td>
                            R$ 60,00/hora
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'VISTORIAS IN SERVICE'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de VISTORIAS IN SERVICE.</p>
                <br/>
                <b>VISTORIA IN SERVICE</b>
                <br/>
                <ol style="list-style-type: lower-alpha;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>INSPEÇÃO DO CONTÊINER SEGUINDO CRITÉRIO DO ARMADOR. </li>
                    <li>REGISTRO FOTOGRÁFICO SEGUINDO INSTRUÇÕES DO ARMADOR.</li>
                </ol>
                <br/>
                <p> Nosso horário limite para efetuar essa modalidade é 18:00 ou término da luz natural.
                    O pedido deverá ser recebido com pelo menos 02 (dois) períodos de antecedência.</p>
                <br/>
                <b>RESPONSABILIDADE DO CLIENTE</b>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1.5rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Informar terminal de vistoria e quantidade de contêineres.</li>
                </ol>
                <br/>
                <b>RESPONSABILIDADE DA SEABOX</b>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1.5rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Efetuar os serviços contratados e informar seus respectivos resultados por e-mail assim que as informações estiverem disponíveis. </li>
                </ol>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            IN SERVICE (depende do volume)
                        </td>
                        <td>
                            R$ 30,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'INVENTÁRIO FÍSICO'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de INVENTÁRIO FÍSICO.</p>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Localização física de estoque de todos os contêineres no terminal, informadas pelo cliente; </li>
                    <li>1 foto do contêiner onde conste a numeração do mesmo (pode ser na pilha quando visível a numeração);</li>
                    <li>Envio de relatório final com fotos e confirmação de estoque;</li>
                    <li>Cópia do EIR para caso de contêiner que tenha sido liberado após o envio da relação de estoque (relação do estoque será enviada no dia anterior à vistoria), vistoriador e responsável pelo terminal devem assinar o relatório de reconciliação;</li>
                </ol>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            INVENTÁRIO FÍSICO
                        </td>
                        <td>
                            R$ 60,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'AUDITORIA DO TERMINAL E DOS PROCESSOS DE VISTORIA'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de AUDITORIA DO TERMINAL E DOS PROCESSOS DE VISTORIA.</p>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Auditoria presencial;</li>
                    <li>Vistoria de 20 unidades “pré-reparo” (10 unidades AVARIADAS posicionadas em oficina de reparo ou escolhidas aleatoriamente nas pilhas e 10 unidades consideradas OK na vistoria do terminal no “gate in”, escolhidas aleatoriamente nas pilhas);</li>
                    <li>Vistoria de 10 unidades “pós-reparo” (posicionadas em oficina de reparo ou escolhidas aleatoriamente nas pilhas); </li>
                    <li>Relatório fotográfico (por unidade);</li>
                </ol>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'AUDITORIA DE TERMINAL'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de AUDITORIA DE TERMINAL.</p>
                <br/>
                <b>AUDITORIA DE PROCESSO</b>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Vistoria física do terminal (informado pelo cliente);</li>
                    <li>Verificação de toda a estrutura física do espaço e de todos os equipamentos;</li>
                    <li>Envio de relatório final com fotos e considerações;</li>
                </ol>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'Vistoria de Condição da unidade'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de Condição da unidade.</p>
                <br/>
                <b>Vistoria de Condição</b>
                <br/>
                <ol style="list-style-type: lower-alpha;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Vistoria da estrutura em contêineres DRY e/ou REEFER;</li>
                    <li>
                        Será informado em relatório próprio da SEABOX as seguintes informações:
                        <ul style="list-style-type: circle;padding-left: 1rem;">
                            <li>Condições gerais da unidade</li>
                            <li>Se o contêiner está apto ao transporte de carga (não estando, será rejeitado a menos se instruído de forma diferente)</li>
                            <li>Se o contêiner atende o padrão Carga Geral ou Alimento</li>
                        </ul>
                    </li>
                    <li>Registro fotográfico das unidades selecionadas;</li>
                    <li>Emissão de relatório de Condição Geral das unidades solicitadas;</li>
                </ol>
                <br/>
                <p style="color: grey;">
                    Nosso horário limite para efetuar essa modalidade é 17:30 ou término da luz natural.
                    Aos Sábados, atendimento até 12:00am.
                    Os pedidos deverão ser recebidos com pelo menos 01 (um) período de antecedência.
                </p>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            RELATÓRIO DE CONDIÇÃO ESTRUTURAL
                        </td>
                        <td>
                            R$ 120,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'Vistoria de Off Hire'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:12px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de vistoria de Off Hire.</p>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Vistoria da estrutura em contêineres DRY e/ou REEFER;</li>
                    <li>Será informado na estimativa do depósito a responsabilidade de cada avaria, efetuando assim a justa distribuição de custos entre locador e locatário.</li>
                    <li>Relatório fotográfico (por unidade);</li>
                </ol>
                <br/>
                <table class="my-table items">
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                            Off Hire
                        </td>
                        <td>
                            R$ 60,00
                        </td>
                    </tr>
                    <tr style="line-height: 1rem; word-spacing: .5rem;color: <?php echo $color; ?>;">
                        <td>
                           Deslocamento
                        </td>
                        <td>
                            R$ 3,50/km
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        <?php endif; ?>
        <?php if($v->title == 'Vistoria Conjunta'): ?>
            <div style="text-align: left; display: flex; gap: 5px;line-height: 18px;flex-direction: column;padding-top: 1rem;font-size:14px;">
                <b>OBJETIVO</b>
                <br/>
                <p style="color: grey">Apresentar proposta comercial para contrato de prestação de serviços de Vistoria Conjunta.</p>
                <br/>
                <ol style="list-style-type: circle;padding-left: 1rem;padding-right: 1rem;text-align: justify;color: grey">
                    <li>Vistoria da estrutura em contêineres DRY e/ou REEFER;</li>
                    <li>Quando um “container” vazio avariado estiver sendo devolvido, acompanhamos a “vistoria juntamente” com o Importador ou seu representante legal, para determinar a justa distribuição de custos entre locador e locatário.</li>
                    <li>Relatório fotográfico (por unidade);</li>
                </ol>
            </div>
        <?php endif; ?>
    <?php endforeach;?>
<?php endforeach;?>
<?php endif; ?>