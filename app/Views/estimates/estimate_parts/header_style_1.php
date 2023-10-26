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
<table class="header-style" style="height: 100vh;">
    <tr class="invoice-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
            <?php echo view('estimates/estimate_parts/company_logo'); ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        
        <td class="invoice-info-container" style="width: 35%; vertical-align: top; text-align: right">
            <?php echo company_contact_widget($estimate_info->company_id, "estimate"); ?>
            <div style="line-height: 5px;"></div>
            <?php if (get_setting("invoice_style") == "style_3") { ?>
                <div style="font-size: 25px; color: grey; margin-bottom: 10px;"><?php echo app_lang("estimate"); ?></div>
                <div style="line-height: 10px;"></div>
                <span class="invoice-meta text-default" style="font-size: 90%; color: grey;font-weight: bold; "><?php echo app_lang("estimate_number") . ": " . get_estimate_number($estimate_info->estimate_number); ?></span><br />
            <?php } else { ?>
                <span style="font-size:20px;color: grey;font-size: 90%;font-family:Barlow Bold, sans-serif;">&nbsp;<?php echo get_estimate_number($estimate_info->estimate_number); ?>&nbsp;</span>
                <div style="line-height: 10px;"></div>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <h1 style="background-color:#17365D; color: white;line-height: 30px; font-family:Barlow ExtraBold, sans-serif;text-align: center;">
                PROPOSTA COMERCIAL
            </h1>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: justify;color: #17365D;font-size: 18px;">
            A <b style="font-family:Barlow Bold, sans-serif;"><?php echo $company_info->name ?></b>, empresa especializada em sistemas de inteligência para segurança, tem a satisfação de apresentar esta proposta comercial para fornecimento e instalação de sistema (DESCREVER O SISTEMA FORNECIDO), para apoiá-lo a alcançar seus objetivos de melhor a e aumento da segurança e rapidez de seus serviços.
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: justify;color: #17365D;font-size: 18px;">
            <br/><br/>
            <b style="font-family:Barlow Bold, sans-serif;">SOBRE A <?php echo $company_info->name ?></b>
            <br/><br/>
            Há mais de 20 anos no mercado, a <b style="font-family:Barlow Bold, sans-serif;"><?php echo $company_info->name ?></b> trabalha com consultoria, equipamentos e sistemas de segurança e monitoramento, sempre trazendo para o mercado as maiores e melhores inovações tecnológicas do setor e representando parceiros internacionais líderes em seus segmentos de atuação.
            <br/><br/>
            Com grande expertise em portos, terminais portuários, retro alfandegários, aeroportos e transportadoras em geral, a <b style="font-family:Barlow Bold, sans-serif;"><?php echo $company_info->name ?></b> avalia, implementa os equipamentos mais adequados para cada caso, faz a gestão do processo e presta suporte no pós-venda.
            <br/><br/>
            Nossas soluções são completas e capazes de atender qualquer mercado em busca de melhorias em seus sistemas de segurança, automação e redução de custos. 
        </td>
    </tr>
    <tr>
        <td colspan="3" class="invoice-info-container">
            <?php
                $data = array(
                    "client_info" => $client_info,
                    "color" => "#17365D",
                    "estimate_info" => $estimate_info
                );
                echo view('estimates/estimate_parts/estimate_to', $data);
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: justify;color: #17365D;font-size: 18px;">
            <br/>
            <b style="font-family:Barlow Bold, sans-serif;">OBJETIVO</b>
            <p>SERVIÇO</p>
            <br/>
        </td>
    </tr>
</table>