<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("termometer_proposals"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="termometer-proposals-widget">
        <div class="row">
            <div class="col-md-6">
                <canvas id="termometer-proposals-chart" style="width: 100%; height: 160px;"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$lead_status_title = array();
$lead_status_data = array();
foreach ($lead_statuses as $lead_status) {
    $lead_status_title[] = $lead_status->title;
    $lead_status_data[] = $lead_status->total;
}
?>
<script>
    //for leads status chart
    var labels = <?php echo json_encode($lead_status_title) ?>;
    var leadStatusData = <?php echo json_encode($lead_status_data) ?>;
    var leadsOverviewChart = document.getElementById("termometer-proposals-chart");
    new Chart(leadsOverviewChart, {
        type: 'doughnut',
        data: {
            labels: ['Quente', 'frio', 'morno'],
            datasets: [
                {
                    data: leadStatusData,
                    borderWidth: 0
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 87,
            tooltips: {
                callbacks: {
                    title: function (tooltipItem, data) {
                        return data['labels'][tooltipItem[0]['index']];
                    },
                    label: function (tooltipItem, data) {
                        return "";
                    },
                    afterLabel: function (tooltipItem, data) {
                        var dataset = data['datasets'][0];
                        var percent = dataset["_meta"][Object.keys(dataset["_meta"])[0]]['cfv_1'];
                        return percent;
                    }
                }
            },
            legend: {
                display: false
            },
            animation: {
                animateScale: true
            }
        }
    });

    $(document).ready(function () {
        initScrollbar('#termometer-proposals-widget', {
            setHeight: 327
        });
    });

</script>