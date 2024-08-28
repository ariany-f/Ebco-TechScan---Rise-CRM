<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("sellers_leads_overview"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="sellers-overview-widget">
        <table>
            <tr style="text-align: center;background-color: rgb(78, 94, 106);color: white;line-height: 45px;">
                <th>Vendedor</th>
                <th>Total Clientes</th>
                <th>Total Leads</th>
                <th>Total Prospects</th>
                <th>Total de Propostas</th>
                <th>Valor Estimado Total</th>
            </tr>
            <?php foreach ($team_members as $member) { ?>
                <tr style="text-align: center;line-height: 45px;">
                    <td class=""><?php echo $member->first_name; ?></td>
                    <td class=""><?php echo $member->total_clients; ?></td>
                    <td class=""><?php echo $member->total_leads; ?></td>
                    <td class=""><?php echo $member->total_prospects; ?></td>
                    <td class=""><?php echo $member->total_projects; ?></td>
                    <td class=""><?php echo to_currency($member->total_sells, 'R$'); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>