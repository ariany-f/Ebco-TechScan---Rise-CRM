<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("sellers_overview"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="sellers-overview-widget">
        <div class="row">
            <div class="col-md-6">
                <canvas id="sellers-overview-chart" style="width: 100%; height: 350px;"></canvas>
            </div>
            <div class="col-md-6 pl20 <?php echo count($team_members) > 8 ? "" : "pt-4"; ?>">
                <?php
                foreach ($team_members as $member) {
                    ?>
                    <div class="pb-2">
                        <div class="color-tag border-circle me-3 wh10"></div><?php echo $member->first_name; ?>
                        <span class="strong float-end"><?php echo to_currency($member->total_sells, 'R$'); ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <!-- <div class="row">
            <div class="col-md-4">
                <div><?php //echo app_lang("total_leads") . ": "; ?> <span class="strong"><?php //echo $total_leads; ?></span></div>
            </div>
            <div class="col-md-4">
                <div><?php// echo app_lang("converted_to_client") . ": "; ?> <span class="strong"><?php //echo $converted_to_client; ?></span></div>
            </div>
            <div class="col-md-4">
                <div><?php// echo app_lang("total_sells") . ": "; ?> <span class="strong"><?php //echo $total_sells; ?></span></div>
            </div>
        </div> -->
    </div>
</div>

<?php
$member_name = array();
$member_data = array();
foreach ($team_members as $member) {
    $member_name[] = $member->first_name;
    $member_data[] = $member->total_sells;
}
?>
<script>
    
    var randomColorGenerator = function () { 
        return "hsl(" + 360 * Math.random() + ',' +
                (25 + 70 * Math.random()) + '%,' + 
                (85 + 10 * Math.random()) + '%)'; 
    };

    function poolColors(a) {
        var pool = [];
        for(i = 0; i < a; i++) {
            pool.push(randomColorGenerator());
        }
        return pool;
    }

    //for sellers status chart
    var labels = <?php echo json_encode($member_name) ?>;
    var sellerStatusData = <?php echo json_encode($member_data) ?>;
    var sellersOverviewChart = document.getElementById("sellers-overview-chart");
    new Chart(sellersOverviewChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    data: sellerStatusData, 
                    backgroundColor: poolColors(sellerStatusData.length),
                    borderWidth: 0
                }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return value.toLocaleString("pt-BR",{style:"currency", currency:"BRL"});
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    title: function (tooltipItem, data) {
                        var value = data['datasets'][0]['data'][tooltipItem[0]['index']];
                        return data['labels'][tooltipItem[0]['index']];
                    },
                    label: function(context, data) {
                        var dataset = data['datasets'][0];
                        var value = dataset['data'][context['index']];
                        console.log(value.toLocaleString("pt-BR",{style:"currency", currency:"BRL"}));

                        return value.toLocaleString("pt-BR",{style:"currency", currency:"BRL"});
                    },
                    afterLabel: function (tooltipItem, data) {
                        return "";
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
        initScrollbar('#sellers-overview-widget', {
            setHeight: 327
        });
    });
</script>