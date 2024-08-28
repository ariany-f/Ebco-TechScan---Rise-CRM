<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="thermometer" class="icon-16"></i> &nbsp;<?php echo app_lang("termometer_proposals"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="termometer-proposals-widget">
        <div class="row">
            <div class="col-md-6">
                <canvas id="termometer-proposals-chart" style="width: 100%; height: 160px;"></canvas>
            </div>
            <div class="col-md-6 pl20 <?php echo count($termometer) > 8 ? "" : "pt-4"; ?>">
                <?php
                foreach ($termometer as $term) {
                    ?>
                    <div class="pb-2" style="display: flex;gap: 15px;">
                        <div class="color-tag border-circle me-3 wh10"></div><?php echo ((!empty($term['title'])) ? $term['title'] : 'Sem Classificação'); ?>
                        <span class="strong" ><?php echo $term['total']; ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
$termometer_title = array();
$termometer_data = array();
$termometer_color = array();
foreach ($termometer as $term) {
    $termometer_title[] = ((!empty($term['title'])) ? $term['title'] : 'Sem Classificação');
    $termometer_data[] = $term['total'];
    $termometer_color[] = $term['color'];
}
?>
<script>

    //for termometer chart
    var labels = <?php echo json_encode($termometer_title) ?>;
    var termometerData = <?php echo json_encode($termometer_data) ?>;
    var termometerColor = <?php echo json_encode($termometer_color) ?>;
    var termometerChart = document.getElementById("termometer-proposals-chart");
    
    new Chart(termometerChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    data: termometerData,
                    backgroundColor: termometerColor,
                    borderWidth: 0
                }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    }
                }]
            },
            responsive: true,
          //  maintainAspectRatio: false,
           // cutoutPercentage: 87,
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
                        return dataset['data'][tooltipItem['index']];
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
        initScrollbar('#termometer_proposals_widget', {
            setHeight: 327
        });
    });

</script>