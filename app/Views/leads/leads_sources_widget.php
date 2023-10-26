<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("leads_source"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="leads-source-widget">
        <table>
            <tr style="text-align: center;background-color: rgb(78, 94, 106);color: white;line-height: 45px;">
                <th>Origem</th>
                <th>Total Clientes</th>
                <th>Valor Total de Vendas</th>
            </tr>
            <?php foreach ($lead_sources as $lead_source) { ?>
                <tr style="text-align: center;line-height: 45px;">
                    <td class=""><?php echo $lead_source->title; ?></td>
                    <td class=""><?php echo $lead_source->total; ?></td>
                    <td class=""><?php echo to_currency($lead_source->projects_total, 'R$'); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>