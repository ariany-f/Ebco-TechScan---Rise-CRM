<?php

namespace App\Models;

class Estimates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimates_table = $this->db->prefixTable('estimates');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');
        $estimate_type_table = $this->db->prefixTable('estimate_type');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');
        $items_table = $this->db->prefixTable('items');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $estimates_table.id=$id";
        }
        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $estimates_table.client_id=$client_id";
        }
        $is_bidding = $this->_get_clean_value($options, "is_bidding");
        if ($is_bidding) {
            $where .= " AND $estimates_table.is_bidding=$is_bidding";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.estimate_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.estimate_value,0))";

        $discountable_estimate_value = "IF($estimates_table.discount_type='after_tax', (IFNULL(items_table.estimate_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.estimate_value,0) )";

        $discount_amount = "IF($estimates_table.discount_amount_type='percentage', IFNULL($estimates_table.discount_amount,0)/100* $discountable_estimate_value, $estimates_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.estimate_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.estimate_value,0)- $discount_amount))";

        $estimate_value_calculation = "(
            IFNULL(items_table.estimate_value,0)+
            IF($estimates_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $estimates_table.status='$status'";
        }

        $exclude_draft = $this->_get_clean_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $estimates_table.status!='draft' ";
        }

        $clients_only = $this->_get_clean_value($options, "clients_only");
        if ($clients_only) {
            $where .= " AND $estimates_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0)";
        }

        $show_own_estimates_only_user_id = $this->_get_clean_value($options, "show_own_estimates_only_user_id");
        if ($show_own_estimates_only_user_id) {
            $where .= " AND $estimates_table.created_by=$show_own_estimates_only_user_id";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("estimates", $custom_fields, $estimates_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT $estimates_table.*, $estimate_type_table.title AS estimate_type, client_contact.id AS contact_id, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name, $projects_table.title as project_title, $clients_table.is_lead,
           CONCAT($users_table.first_name, ' ',$users_table.last_name) AS signer_name, $users_table.email AS signer_email,
           $estimate_value_calculation AS estimate_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
        FROM $estimates_table
        LEFT JOIN $clients_table ON $clients_table.id= $estimates_table.client_id        
        LEFT JOIN $users_table AS client_contact ON client_contact.client_id = $clients_table.id AND client_contact.deleted=0 AND client_contact.is_primary_contact=1 
        LEFT JOIN $estimate_type_table ON $estimate_type_table.id= $estimates_table.estimate_type_id
        LEFT JOIN $projects_table ON $projects_table.id= $estimates_table.project_id
        LEFT JOIN $users_table ON $users_table.id= $estimates_table.accepted_by
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $estimates_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $estimates_table.tax_id2 
        LEFT JOIN (SELECT estimate_id, SUM(total) AS estimate_value, $items_table.files AS estimate_files FROM $estimate_items_table INNER JOIN $items_table ON $items_table.id = $estimate_items_table.item_id WHERE $estimate_items_table.deleted=0 GROUP BY estimate_id) AS items_table ON items_table.estimate_id = $estimates_table.id 
        $join_custom_fieds
        WHERE $estimates_table.deleted=0 $where $custom_fields_where";
        return $this->db->query($sql);
    }
    

    function is_duplicate_code($estimate_number, $id = 0) {

        $result = $this->get_all_regex(array("estimate_number" => $estimate_number), 1000000, 0, $estimate_number);


        if (count($result->getResult()) && $result->getRow()->id != $id) {
            return $result->getRow();
        } else {
            return false;
        }
    }

    function get_estimate_total_summary($estimate_id = 0) {
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $estimates_table = $this->db->prefixTable('estimates');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');

        $item_sql = "SELECT SUM($estimate_items_table.total) AS estimate_subtotal
        FROM $estimate_items_table
        LEFT JOIN $estimates_table ON $estimates_table.id= $estimate_items_table.estimate_id    
        WHERE $estimate_items_table.deleted=0 AND $estimate_items_table.estimate_id=$estimate_id AND $estimates_table.deleted=0";
        $item = $this->db->query($item_sql)->getRow();

        $estimate_sql = "SELECT $estimates_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $estimates_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $estimates_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $estimates_table.tax_id2
        WHERE $estimates_table.deleted=0 AND $estimates_table.id=$estimate_id";
        $estimate = $this->db->query($estimate_sql)->getRow();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$estimate->client_id";
        $client = $this->db->query($client_sql)->getRow();

        $result = new \stdClass();
        $result->estimate_subtotal = $item->estimate_subtotal;
        $result->tax_percentage = $estimate->tax_percentage;
        $result->tax_percentage2 = $estimate->tax_percentage2;
        $result->tax_name = $estimate->tax_name;
        $result->tax_name2 = $estimate->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $estimate_subtotal = $result->estimate_subtotal;
        $estimate_subtotal_for_taxes = $estimate_subtotal;
        if ($estimate->discount_type == "before_tax") {
            $estimate_subtotal_for_taxes = $estimate_subtotal - ($estimate->discount_amount_type == "percentage" ? ($estimate_subtotal * ($estimate->discount_amount / 100)) : $estimate->discount_amount);
        }

        if ($estimate->tax_percentage) {
            $result->tax = $estimate_subtotal_for_taxes * ($estimate->tax_percentage / 100);
        }
        if ($estimate->tax_percentage2) {
            $result->tax2 = $estimate_subtotal_for_taxes * ($estimate->tax_percentage2 / 100);
        }
        $estimate_total = $item->estimate_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($estimate->discount_type == "after_tax") {
            $estimate_subtotal = $estimate_total;
        }

        $result->discount_total = $estimate->discount_amount_type == "percentage" ? ($estimate_subtotal * ($estimate->discount_amount / 100)) : $estimate->discount_amount;

        $result->discount_type = $estimate->discount_type;

        $result->discount_total = is_null($result->discount_total) ? 0 : $result->discount_total;
        $result->estimate_total = $estimate_total - number_format($result->discount_total, 2, ".", "");

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");
        return $result;
    }

    //get estimate last id
    function get_estimate_last_id() {
        $estimates_table = $this->db->prefixTable('estimates');

        $sql = "SELECT MAX($estimates_table.id) AS last_id FROM $estimates_table";

        return $this->db->query($sql)->getRow()->last_id;
    }

    //save initial number of estimate
    function save_initial_number_of_estimate($value) {
        $estimates_table = $this->db->prefixTable('estimates');

        $sql = "ALTER TABLE $estimates_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    function estimate_sent_statistics($options = array()) {
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $clients_table = $this->db->prefixTable('clients');

        $info = new \stdClass();
        $year = get_my_local_time("Y");

        $where = "";
        
        $estimate_where = $this->_get_clients_of_currency_query($this->_get_clean_value($options, "currency_symbol"), $estimates_table, $clients_table);

        $estimate_value_calculation_query = $this->_get_estimate_value_calculation_query($estimates_table);

        $estimates = "SELECT SUM(total) AS total, MONTH(valid_until) AS month FROM (SELECT $estimate_value_calculation_query AS total ,$estimates_table.valid_until
            FROM $estimates_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $estimates_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $estimates_table.tax_id2
            LEFT JOIN (SELECT estimate_id, SUM(total) AS estimate_value FROM $estimate_items_table WHERE deleted=0 GROUP BY estimate_id) AS items_table ON items_table.estimate_id = $estimates_table.id 
            WHERE $estimates_table.deleted=0 AND ($estimates_table.status <> 'accepted' AND $estimates_table.status <> 'rejected' ) $where AND YEAR($estimates_table.valid_until)=$year $estimate_where) as details_table
            GROUP BY  MONTH(valid_until)";

        $info->estimates = $this->db->query($estimates)->getResult();
        $info->currencies = $this->get_used_currencies_of_client()->getResult();

        return $info;
    }

    //get total estimate value calculation query
    protected function _get_estimate_value_calculation_query($estimates_table) {
        $select_estimate_value = "IFNULL(items_table.estimate_value,0)";

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*$select_estimate_value)";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*$select_estimate_value)";

        $discountable_estimate_value = "IF($estimates_table.discount_type='after_tax', ($select_estimate_value + $after_tax_1 + $after_tax_2), $select_estimate_value )";

        $discount_amount = "IF($estimates_table.discount_amount_type='percentage', IFNULL($estimates_table.discount_amount,0)/100* $discountable_estimate_value, $estimates_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* ($select_estimate_value- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* ($select_estimate_value- $discount_amount))";

        $estimate_value_calculation_query = "(
                $select_estimate_value+
                IF($estimates_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
                - $discount_amount
               )";

        return $estimate_value_calculation_query;
    }

    function get_used_currencies_of_client() {
        $clients_table = $this->db->prefixTable('clients');
        $default_currency = get_setting("default_currency");

        $sql = "SELECT $clients_table.currency
            FROM $clients_table
            WHERE $clients_table.deleted=0 AND $clients_table.currency!='' AND $clients_table.currency!='$default_currency'
            GROUP BY $clients_table.currency";

        return $this->db->query($sql);
    }

    /**
     * Adicionar custom field para o cf-4 ao buscar vendedores
     */
    function get_sellers_estimates($options = array()) {

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $where = "";
        $seller = $this->_get_clean_value($options, "seller_id");
        if ($seller) {
            $where .= " AND u.id=$seller";
        }

        $pos = $this->_get_clean_value($options, "pos");
        if ($pos) {
            $where .= " AND u.role_id=10";
        }
        else
        {
            $where .= " AND u.role_id <> 10";
        }
        
        $month = $this->_get_clean_value($options, "month");
        if ($month) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%m') =$month";
        }

        $year = $this->_get_clean_value($options, "year");
        if ($year) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%Y') =$year";
        }
        
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND (e.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            'main_data.Mes',
            'main_data.Vendedor'
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $sql = "SELECT 
            DATE_FORMAT(STR_TO_DATE(main_data.Mes, '%Y-%m'), '%M') AS 'Mes',
            main_data.Vendedor,
            main_data.PropostasEmitidas AS 'Propostas_Emitidas',
            main_data.PropostasFechadas AS 'Propostas_Fechadas',
            main_data.PercentualConversao AS 'Conversao',
            CONCAT('R$', FORMAT(IFNULL(valor_data.ValorEmitido, 0), 2, 'de_DE')) AS 'Valor_Emitido',
            CONCAT('R$', FORMAT(IFNULL(valor_data.ValorFechado, 0), 2, 'de_DE')) AS 'Valor_Fechado'
        FROM
            (
                SELECT 
                    DATE_FORMAT(e.estimate_date, '%Y-%m') AS 'Mes',
                    CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type) AS 'Vendedor',
                    COUNT(DISTINCT e.id) AS 'PropostasEmitidas',
                    SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) AS 'PropostasFechadas',
                    CONCAT(ROUND(SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT e.id), 0), 2), '%') AS 'PercentualConversao'
                FROM 
                    crm_estimates e
                JOIN 
                    crm_users u ON e.created_by = u.id
                WHERE 
                    e.deleted = 0
                    $where 
                GROUP BY 
                    DATE_FORMAT(e.estimate_date, '%Y-%m'), CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type)
            ) AS main_data
        LEFT JOIN 
            (
                SELECT 
                    DATE_FORMAT(e.estimate_date, '%Y-%m') AS 'Mes',
                    CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type) AS 'Vendedor',
                    SUM(ei.quantity * ei.rate) AS 'ValorEmitido',
                    SUM(CASE WHEN e.status = 'accepted' THEN ei.quantity * ei.rate ELSE 0 END) AS 'ValorFechado'
                FROM 
                    crm_estimates e
                JOIN 
                    crm_users u ON e.created_by = u.id
                LEFT JOIN 
                    crm_estimate_items ei ON e.id = ei.estimate_id
                LEFT JOIN 
                    crm_custom_field_values cfv ON e.id = cfv.related_to_id AND cfv.related_to_type = 'estimates'
                LEFT JOIN 
                    crm_custom_fields cf ON cfv.custom_field_id = cf.id AND cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
                WHERE 
                    e.deleted = 0
                    $where
                GROUP BY 
                    DATE_FORMAT(e.estimate_date, '%Y-%m'), CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type)
            ) AS valor_data
        ON main_data.Mes = valor_data.Mes AND main_data.Vendedor = valor_data.Vendedor
        $order $limit_offset;";

        $raw_query = $this->db->query($sql);
        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        if ($limit) {
            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    function get_conversion_data($options = array(), $date_start, $date_end) {
        $where = "";
        
        $month = $this->_get_clean_value($options, "month");
        if ($month) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%m') = $month";
        }
    
        $year = $this->_get_clean_value($options, "year");
        if ($year) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%Y') = $year";
        }
        
        if ($date_start && $date_end) {
            $where .= " AND (e.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }
    
        $sql = "
            SELECT 
                ROUND(
                    (SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) * 100.0) / NULLIF(COUNT(DISTINCT e.id), 0), 
                    2
                ) AS 'PercentualConversao'
            FROM 
                crm_estimates e
            WHERE 
                e.deleted = 0
                AND e.is_bidding = 0
                $where
        ";
    
        $result = $this->db->query($sql)->getRow();
    
        return $result ? $result->PercentualConversao : 0;
    }
    

    function get_conversion_bidding_data($options = array()) {
        $where = "";
        
        $month = $this->_get_clean_value($options, "month");
        if ($month) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%m') = $month";
        }
    
        $year = $this->_get_clean_value($options, "year");
        if ($year) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%Y') = $year";
        }
        
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND (e.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }
    
        $sql = "
            SELECT 
                ROUND(
                    (SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) * 100.0) / NULLIF(COUNT(DISTINCT e.id), 0), 
                    2
                ) AS 'PercentualConversao'
            FROM 
                crm_estimates e
            WHERE 
                e.deleted = 0
                AND e.is_bidding = 1
                $where
        ";
    
        $result = $this->db->query($sql)->getRow();
    
        return $result ? $result->PercentualConversao : 0;
    }
    

    function get_coligadas_estimates($options = array()) {
        
        $this->db->query('SET SQL_BIG_SELECTS=1');
        
        $where = "";  

        $coligada = $this->_get_clean_value($options, "coligada");
        if ($coligada) {
            $where .= " AND e.company_id=$coligada";
        }
      
        $month = $this->_get_clean_value($options, "month");
        if ($month) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%m') =$month";
        }

        $year = $this->_get_clean_value($options, "year");
        if ($year) {
            $where .= " AND DATE_FORMAT(e.estimate_date, '%Y') =$year";
        }
        
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND (e.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            'main_data.Mes'
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $sql = "SELECT 
                    DATE_FORMAT(STR_TO_DATE(main_data.Mes, '%Y-%m'), '%M') AS 'Mes',
                    main_data.company_id AS 'ID_Empresa',
                    main_data.company_name AS 'Nome_Empresa',
                    main_data.PropostasEmitidas AS 'Propostas_Emitidas',
                    main_data.PropostasFechadas AS 'Propostas_Fechadas',
                    main_data.PercentualConversao AS 'Conversao',
                    CONCAT('R$', FORMAT(IFNULL(valor_data.ValorEmitido, 0), 2, 'de_DE')) AS 'Valor_Emitido',
                    CONCAT('R$', FORMAT(IFNULL(valor_data.ValorFechado, 0), 2, 'de_DE')) AS 'Valor_Fechado'
                FROM
                    (
                        SELECT 
                            DATE_FORMAT(e.estimate_date, '%Y-%m') AS 'Mes',
                            e.company_id,
                            c.name AS 'company_name',
                            COUNT(DISTINCT e.id) AS 'PropostasEmitidas',
                            SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) AS 'PropostasFechadas',
                            CONCAT(ROUND(SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT e.id), 0), 2), '%') AS 'PercentualConversao'
                        FROM 
                            crm_estimates e
                        JOIN 
                            crm_company c ON e.company_id = c.id
                        WHERE 
                            e.deleted = 0
                            $where
                        GROUP BY 
                            DATE_FORMAT(e.estimate_date, '%Y-%m'), e.company_id, c.name
                    ) AS main_data
                LEFT JOIN 
                    (
                        SELECT 
                            DATE_FORMAT(e.estimate_date, '%Y-%m') AS 'Mes',
                            e.company_id,
                            SUM(ei.quantity * ei.rate) AS 'ValorEmitido',
                            SUM(CASE WHEN e.status = 'accepted' THEN ei.quantity * ei.rate ELSE 0 END) AS 'ValorFechado'
                        FROM 
                            crm_estimates e
                        JOIN 
                            crm_company c ON e.company_id = c.id
                        LEFT JOIN 
                            crm_estimate_items ei ON e.id = ei.estimate_id
                        LEFT JOIN 
                            crm_custom_field_values cfv ON e.id = cfv.related_to_id AND cfv.related_to_type = 'estimates'
                        LEFT JOIN 
                            crm_custom_fields cf ON cfv.custom_field_id = cf.id AND cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
                        WHERE 
                            e.deleted = 0
                            $where
                        GROUP BY 
                            DATE_FORMAT(e.estimate_date, '%Y-%m'), e.company_id
                    ) AS valor_data
                ON main_data.Mes = valor_data.Mes AND main_data.company_id = valor_data.company_id
            $order $limit_offset;";

        $raw_query = $this->db->query($sql);
        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        if ($limit) {
            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    function count_total_emmited_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.status <> 'accepted' AND $estimates_table.is_bidding = 0 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_approved_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.status = 'accepted' AND $estimates_table.is_bidding = 0 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_emmited_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.status <> 'accepted' AND $estimates_table.is_bidding = 1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_approved_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.status = 'accepted' AND $estimates_table.is_bidding = 1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_amount_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        
        $where = "";

        $item = $this->_get_clean_value($options, "item");
        if ($item) {
            $item_txt = "AND (";
            foreach($item as $k => $it)
            {
                $item_txt .= "($estimate_items_table.title LIKE '%$it%' OR $estimate_items_table.description LIKE '%$it%')";

                if(($k+1) < count($item))
                {
                    $item_txt .= " OR";
                }
                else
                {
                    $item_txt .= ")";
                }
            }
            $where .= $item_txt;
        }
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        
        $sql = "SELECT SUM($estimate_items_table.quantity * $estimate_items_table.rate) AS total
                FROM $estimate_items_table
                INNER JOIN $estimates_table ON $estimate_items_table.estimate_id = $estimates_table.id AND $estimates_table.deleted = 0 AND $estimates_table.status = 'accepted' AND $estimates_table.is_bidding = 0
                WHERE $estimate_items_table.deleted = 0 $where";
        
        return $this->db->query($sql)->getRow()->total;
    }
    
    function count_total_amount_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        
        $where = "";

        $item = $this->_get_clean_value($options, "item");
        if ($item) {
            $item_txt = "AND (";
            foreach($item as $k => $it)
            {
                $item_txt .= "($estimate_items_table.title LIKE '%$it%' OR $estimate_items_table.description LIKE '%$it%')";

                if(($k+1) < count($item))
                {
                    $item_txt .= " OR";
                }
                else
                {
                    $item_txt .= ")";
                }
            }
            $where .= $item_txt;
        }

        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }

        
        $sql = "SELECT SUM($estimate_items_table.quantity * $estimate_items_table.rate) AS total
                FROM $estimate_items_table
                INNER JOIN $estimates_table ON $estimate_items_table.estimate_id = $estimates_table.id AND $estimates_table.deleted = 0 AND $estimates_table.status = 'accepted' AND $estimates_table.is_bidding = 1
                WHERE $estimate_items_table.deleted = 0 $where";
        
        return $this->db->query($sql)->getRow()->total;
    }
}
