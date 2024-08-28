<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("leads_overview"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="leads-overview-widget">
        <div class="row">
            <div class="col-md-12">
                <!-- <canvas id="leads-overview-chart" style="width: 100%; height: 160px;"></canvas> -->
                <div id='funnelChart' style="width: 100%; height: 430px;"></div>
            </div>
            <!-- <div class="col-md-5 pl20 <?php //echo count($lead_statuses) > 8 ? "" : "pt-4"; ?>" style="display: flex;flex-direction: column;justify-content: center;">
                <?php
                foreach ($lead_statuses as $lead_status) {
                ?>
                    <div class="pb-2" style="display: flex;gap: 15px;">
                        <div class="color-tag border-circle me-3 wh10" style="background-color: <?php //echo $lead_status->color; ?>;"></div><?php //echo $lead_status->title; ?>
                        <span class="strong" style="color: <?php //echo $lead_status->color; ?>"><?php //echo $lead_status->total; ?></span><?php //echo (($lead_status->title !== 'Lead') ? to_currency($lead_status->projects_total, "R$ ") : "<i data-feather='slash' class='icon-16'></i>"); ?>
                    </div>
                <?php
                }
                foreach ($client_statuses as $lead_status) {
                    ?>
                        <div class="pb-2" style="display: flex;gap: 15px;">
                            <div class="color-tag border-circle me-3 wh10" style="background-color: <?php //echo $lead_status->color; ?>;"></div><?php //echo $lead_status->title; ?>
                            <span class="strong" style="color: <?php //echo $lead_status->color; ?>"><?php //echo $lead_status->total; ?></span><?php //echo to_currency($lead_status->projects_total, "R$ "); ?>
                        </div>
                    <?php
                    }
                ?>
            </div> -->
        </div>
        <!-- <div class="row">
            <div class="col-md-12">
                <div><?php //echo app_lang("total_sells") . ": "; ?> <span class="strong"><?php //echo $total_sells; ?></span></div>
            </div>
        </div> -->
    </div>
</div>


<?php
$lead_status_title = array();
$lead_status_data = array();
$lead_status_color = array();
$empty = array();

foreach ($lead_statuses as $lead_status) {
    $lead_status_title[] = $lead_status->title;
    $lead_status_data[] = $lead_status->total;
    $lead_status_color[] = $lead_status->color;
    $empty[] = "";
}
foreach ($client_statuses as $client_status) {
    $lead_status_title[] = $client_status->title;
    $lead_status_data[] = $client_status->total;
    $lead_status_color[] = $client_status->color;
    $empty[] = "";
}
?>
<script>
    //for leads status chart
    var labels = <?php echo json_encode($lead_status_title) ?>;
    var leadStatusData = <?php echo json_encode($lead_status_data) ?>;
    var leadStatusColor = <?php echo json_encode($lead_status_color) ?>;
    var yLabels = <?php echo json_encode($empty) ?>;

    var gd = document.getElementById('funnelChart');
    var data = [{
        type: 'funnel',
        x: leadStatusData,
        connector: {line: {color: "royalblue", dash: "dot", width: 3}},
        text: labels,
        hoverinfo: 'x+percent total', opacity: 0.9, marker: {
            color: leadStatusColor
        }
    }];
    var layout = {funnelmode: "stack", showlegend: 'True', height: 450}
    //var layout = {margin: {l: 100},height: 400}

    Plotly.newPlot('funnelChart', data, layout);
</script>