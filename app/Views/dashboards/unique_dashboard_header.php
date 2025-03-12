<?php
if (!isset($dashboard_info)) {
    $dashboard_info = new stdClass();
}

$title = app_lang("dashboard");
$color = "#fff";
$selected_dashboard = "border-circle";
if ($dashboard_type == "custom" && $dashboard_info->id !== get_setting("staff_default_dashboard")) {
    $title = $dashboard_info->title;
    $color = $dashboard_info->color;
    $selected_dashboard = "";
}
?>

<div class="clearfix mb15 dashbaord-header-area">

    <div class="clearfix float-start">
        <span class="float-start p10 pl0">
            <span style="background-color: <?php echo $color; ?>" class="color-tag border-circle"></span>
        </span>
        <h4 class="float-start"><?php echo $title; ?></h4>
    </div>        

    <!-- <div class="float-end clearfix">
        <span class="float-end" id="dashboards-color-tags">
            <div style="display: flex;">
                Filtro para Datas dos gráficos
                <div class="row" style="align-items: center;">
                    <label for="daterange" class="col-md-2">Filtrar Período</label>
                    <div class="col-md-6">
                        <input class="form-control" type="text" name="daterange" id="date_range" value="" />
                        <input type="hidden" id="date_start" value="">
                        <input type="hidden" id="date_end" value="">
                    </div>
                    
                    <div class="col-md-4">
                        <button type="button" id="clean-filter" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle icon-16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Limpar Filtro</button>
                    </div>
                </div>
            </span>
        </div>
    </div> -->
    
    <div class="float-end clearfix">
        <span class="float-end dropdown dashboard-dropdown ml10">
            <div class="dropdown-toggle clickable" data-bs-toggle="dropdown" aria-expanded="true" >
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end mt-1" role="menu">
                <?php if ($dashboard_type == "default" || (!$login_user->is_admin && $dashboard_info->id === get_setting("staff_default_dashboard"))) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("dashboard/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_new_dashboard'), array("title" => app_lang('add_new_dashboard'), "class" => "dropdown-item")); ?> </li>
                <?php } else { ?>
                    <li role="presentation" class="hidden-xs"><?php echo anchor(get_uri("dashboard/edit_dashboard/" . $dashboard_info->id), "<i data-feather='columns' class='icon-16'></i> " . app_lang('edit_dashboard'), array("title" => app_lang('edit_dashboard'), "class" => "dropdown-item")); ?> </li>
                    <li role="presentation"><?php echo modal_anchor(get_uri("dashboard/modal_form/" . $dashboard_info->id), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_title'), array("title" => app_lang('edit_title'), "id" => "dashboard-edit-title-button", "class" => "dropdown-item")); ?> </li>

                    <?php echo view("dashboards/mark_as_default_button"); ?>

                    <li role="presentation"><?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array('title' => app_lang('delete'), "class" => "delete dropdown-item", "data-id" => $dashboard_info->id, "data-action-url" => get_uri("dashboard/delete"), "data-action" => "delete-confirmation", "data-success-callback" => "onDashboardDeleteSuccess")); ?> </li>
                <?php } ?>
            </ul>
        </span>
        <span class="float-end" id="dashboards-color-tags">
            <?php 
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
            ?>
            <?php
                // echo anchor(get_uri("dashboard"), "<span class='clickable p10 mr5 inline-block'><span style='background-color: #fff' class='color-tag $selected_dashboard'  title='" . app_lang("default_dashboard") . "'></span></span>");

                // if ($dashboards) {
                //     foreach ($dashboards as $dashboard) {
                //         $selected_dashboard = "";

                //         if ($dashboard_type == "custom") {
                //             if ($dashboard_info->id == $dashboard->id) {
                //                 $selected_dashboard = "border-circle";
                //             }
                //         }

                //         $color = $dashboard->color ? $dashboard->color : "#83c340";

                //         echo anchor(get_uri("dashboard/view/" . $dashboard->id), "<span class='clickable p10 mr5 inline-block'><span style='background-color: $color' class='color-tag $selected_dashboard' title='$dashboard->title'></span></span>");
                //     }
                // }
            ?>
        </span>
        <span id="monthly-date-range-selector" class="float-end"></span>
        <button type="button" id="clean-filter" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle icon-16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Limpar Filtro</button>
    </div>
</div>
<script>
   $(document).ready(function () {
        $('.tqm-events-filter__button--date').on('cancel.daterangepicker', function(ev, picker) {
            $('input[name="daterange"]').val('');
            window.location.href =
                    "https://" +
                    window.location.host +
                    window.location.pathname;
        });

        $("#clean-filter").on('click', function() {
            $('input[name="daterange"]').val('');
            window.location.href =
                    "https://" +
                    window.location.host +
                    window.location.pathname;
        })

        $(document).ready(function () {
            // Inicialização das variáveis de data
            let params = (new URL(document.location)).searchParams;
          
            let date_start = moment().startOf('month');  // Primeiro dia do mês atual
            let date_end = date_start.clone().endOf('month');  // Último dia do mês seguinte
            
            if (params.size > 0) {
                date_start = moment(params.get("date_start"), 'DD-MM-YYYY');
                date_end = moment(params.get("date_end"), 'DD-MM-YYYY');
            }

            var options = {
                dateRangeType: "monthly",
                filterParams: {},
            };
            
            var defaults = {
                dateRangeType: "monthly",
                filterParams: {},
                onChange: function (dateRange) {
                    // Atualizando parâmetros e redirecionando para a nova URL
                    var date_starts = moment(dateRange.start_date).format('DD-MM-YYYY');
                    var date_ends = moment(dateRange.end_date).format('DD-MM-YYYY');
                    var params = [
                        "date_start=" + date_starts,
                        "date_end=" + date_ends
                    ];

                    window.location.href =
                        "https://" +
                        window.location.host +
                        window.location.pathname +
                        '?' + params.join('&');
                },
                onInit: function (dateRange) {
                    
                },
            };
            
            var settings = $.extend({}, defaults, options);
            settings._inputDateFormat = "YYYY-MM-DD";

            var $instance = $("#monthly-date-range-selector");
            var dom = '<div class="ml15">'
                    + '<button data-act="prev" class="btn btn-default date-range-selector"><i data-feather="chevron-left" class="icon-16"></i></button>'
                    + '<button data-act="datepicker" class="btn btn-default" style="margin: -1px"></button>'
                    + '<button data-act="next"  class="btn btn-default date-range-selector"><i data-feather="chevron-right" class="icon-16"></i></button>'
                    + '</div>';
            
            $instance.append(dom);
            
            var $datepicker = $instance.find("[data-act='datepicker']");
            var $dateRangeSelector = $instance.find(".date-range-selector");

            // Função para inicializar o texto do mês
            var initMonthSelectorText = function ($elector) {
                $elector.html(moment(date_start).format("MMMM YYYY"));
            };

            initMonthSelectorText($datepicker);

            // Inicializando o datepicker
            $datepicker.datepicker({
                format: "yyyy-mm",  // Formato para exibir mês e ano
                viewMode: "months", // Exibir apenas meses
                minViewMode: "months", // Evitar a visualização de dias
                autoclose: true,    // Fechar o datepicker após a seleção
                startView: 1,       // Começar no mês (não no ano)
                maxViewMode: 1,     // Não permitir o modo de visualização de anos
                language: "en",     // Definir o idioma (pode ser alterado conforme necessário)
                startDate: date_start.format("YYYY-MM-DD"), // Definir a data de início como start_date
                endDate: date_end.format('YYYY-MM-DD')
            }).on('changeDate', function (e) {
               
                var date = moment(e.date).format(settings._inputDateFormat);
                        var daysInMonth = moment(date).daysInMonth(),
                                yearMonth = moment(date).format("YYYY-MM");
                        settings.filterParams.start_date = yearMonth + "-01";
                        settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
                        initMonthSelectorText($datepicker);
                        settings.onChange(settings.filterParams);
            });

            
            //init default date
            var year = moment(date_start).format("YYYY-MM");
            settings.filterParams.start_date = year + "-01";
            settings.filterParams.end_date = year + "-31";
            settings.filterParams.year = year;
            settings.onInit(settings.filterParams);

            $dateRangeSelector.click(function () {
                var type = $(this).attr("data-act"),
                    startDate = moment(settings.filterParams.start_date),
                    endDate = moment(settings.filterParams.end_date);
                
                if (type === "next") {
                    var nextMonth = startDate.add(1, 'months'),
                            daysInMonth = nextMonth.daysInMonth(),
                            yearMonth = nextMonth.format("YYYY-MM");

                    startDate = yearMonth + "-01";
                    endDate = yearMonth + "-" + daysInMonth;

                } else if (type === "prev") {
                    var lastMonth = startDate.subtract(1, 'months'),
                            daysInMonth = lastMonth.daysInMonth(),
                            yearMonth = lastMonth.format("YYYY-MM");

                    startDate = yearMonth + "-01";
                    endDate = yearMonth + "-" + daysInMonth;
                }

                settings.filterParams.start_date = startDate;
                settings.filterParams.end_date = endDate;
                settings.filterParams.year = moment(startDate).format("YYYY-MM");

                initMonthSelectorText($datepicker);
                settings.onChange(settings.filterParams);
            });
        });

        //modify design for mobile devices
        if (isMobile()) {
            var $dashboardTags = $("#dashboards-color-tags"),
                    $dashboardTagsClone = $dashboardTags.clone(),
                    $dashboardDropdown = $(".dashboard-dropdown .dropdown-menu");

            $dashboardTags.addClass("hide");
            $dashboardTagsClone.removeClass("float-end");
            $dashboardTagsClone.children("span").addClass("p5 text-center inline-block");

            $dashboardTagsClone.children("span").find("a").each(function () {
                $(this).children("span").removeClass("p10").addClass("p5");
            });

            var liDom = "<li id='color-tags-container-for-mobile' class='bg-off-white text-center'></li>"
            $dashboardDropdown.prepend(liDom);
            $("#color-tags-container-for-mobile").html($dashboardTagsClone);
        }
        
        $(function() {
            // Filtro para Datas dos gráficos
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoUpdateInput: false
            }, function(start, end, label) {
                var date_start = start.format('DD-MM-YYYY');
                var date_end = end.format('DD-MM-YYYY');
                var params = [
                    "date_start="+date_start,
                    "date_end=" + date_end
                ];

                window.location.href =
                    "https://" +
                    window.location.host +
                    window.location.pathname +
                    '?' + params.join('&');
            });
        });
    });
</script>