<table class="header-style">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "estimate_info" => $estimate_info
            );
            echo view('estimates/estimate_parts/estimate_info', $data);
            ?>
        </td>
        <td style="width: 40%; vertical-align: top;">
            <?php
            echo view('estimates/estimate_parts/estimate_from', $data);
            ?>
        </td>
    </tr>
</table>