<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("sellers_conversions_overview"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="sellers-overview-widget">
        <table>
            <tr style="text-align: center;background-color: rgb(78, 94, 106);color: white;line-height: 45px;">
                <th>Vendedor</th>
                <th>Total de Propostas</th>
                <th>Total de Vendas</th>
                <th>% de conversão</th>
            </tr>
            <?php foreach ($team_members as $member) { ?>
                <tr style="text-align: center;line-height: 45px;">
                    <td class=""><?php echo $member['first_name']; ?></td>
                    <td class=""><?php echo to_currency($member['total_estimates'], 'R$'); ?></td>
                    <td class=""><?php echo to_currency($member['total_sells'], 'R$'); ?></td>
                    <td class=""><?php echo $member['conversion_percent'] . '%'; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>