<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("leads_source"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="leads-source-widget">
        <div class="row">
            <!-- <table>
                <tr style="text-align: center;background-color: rgb(78, 94, 106);color: white;line-height: 45px;">
                    <th>Origem</th>
                    <th>Total Leads</th>
                    <th>Total Clientes</th>
                    <th>Valor Total de Vendas</th>
                </tr>
                <?php foreach ($lead_sources as $lead_source) { ?>
                    <tr style="text-align: center;line-height: 45px;">
                        <td class=""><?php //echo $lead_source->title; ?></td>
                        <td class=""><?php //echo $lead_source->total_leads; ?></td>
                        <td class=""><?php //echo $lead_source->total_clients; ?></td>
                        <td class=""><?php //echo to_currency($lead_source->projects_total, 'R$'); ?></td>
                    </tr>
                <?php } ?>
            </table> -->
        </div>
        <div class="row">
            <canvas id="leads-sources-chart" style="width: 100%; height: 600px;"></canvas>
        </div>
    </div>
</div>
<?php
$source = array();
$client_data = array();
$lead_data = array();
$source_value = array();
foreach ($lead_sources as $term) {
    $source[] = ((!empty($term->title)) ? $term->title : 'Outro');
    $client_data[] = $term->total_clients;
    $lead_data[] = $term->total_leads;
    $source_value[((!empty($term->title)) ? $term->title : 'Outro')] = to_currency($term->projects_total, 'R$');
}
?>
<script>

    //for leadSources chart
    var labels = <?php echo json_encode($source) ?>;
    var leadSourcesDataClients = <?php echo json_encode($client_data) ?>;
    var leadSourcesDataLeads = <?php echo json_encode($lead_data) ?>;
    var leadSourceValues = <?php echo json_encode($source_value) ?>;
    var leadSourcesChart = document.getElementById("leads-sources-chart");

    new Chart(leadSourcesChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Clientes',
                    backgroundColor: '#6690F4',
                    data: leadSourcesDataClients,
                    borderWidth: 0
                },
                {
                    label: 'Leads',
                    backgroundColor: '#FFB822',
                    data: leadSourcesDataLeads,
                    borderWidth: 0
                }
            ]
        },
        options: {
            scales: {
                xAxes: [{
                    ticks: {
                        callback: function(value) {
                            return [value, leadSourceValues[value]];
                        }
                    }
                }]
            },
            responsive: true,
            maintainAspectRatio: false,
            // cutoutPercentage: 87,
            tooltips: {
                callbacks: {
                    title: function (tooltipItem, data) {
                        return data['datasets'][tooltipItem[0]['datasetIndex']]['label'];
                    },
                    label: function (tooltipItem, data) {
                        return "";
                    },
                    afterLabel: function (tooltipItem, data) {
                        var dataset = data['datasets'][0];
                        return dataset['data'][tooltipItem['index']];
                    }
                }
            },
            legend: {
                display: true
            },
            animation: {
                onComplete: () => {
                    delayed = true;
                },
                delay: (context) => {
                    let delay = 0;
                    if (context.type === 'data' && context.mode === 'default' && !delayed) {
                    delay = context.dataIndex * 2000 + context.datasetIndex * 100;
                    }
                    return delay;
                },
            }
        }
    });

    $(document).ready(function () {
        // initScrollbar('#leadSources_proposals_widget', {
        //     setHeight: 327
        // });
    });

</script>