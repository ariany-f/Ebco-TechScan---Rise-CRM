<?php

namespace App\Models;

use CodeIgniter\Model;

class Estimates_model extends Crud_model {

    protected $table = null;
    protected $Users_model = null;

    function __construct() {
        $this->table = 'estimates';
        $this->Users_model = model("App\Models\Users_model");
        parent::__construct($this->table);
    }

    function next_id() {
        $estimates_table = $this->db->prefixTable('estimates');
        $sql = "SELECT MAX($estimates_table.estimate_number) + 1 as id FROM $estimates_table;";
        return $this->db->query($sql);
    }

    function next_zk_id() {
        $estimates_table = $this->db->prefixTable('estimates');
    
        // Consulta para obter o maior número de sequência do ano atual
        $sql = "
            SELECT 
                COALESCE(MAX(CAST(SUBSTRING_INDEX(estimate_number_temp, '_', -1) AS UNSIGNED)), 0) + 1 AS next_sequence
            FROM 
                $estimates_table
            WHERE 
                estimate_number_temp LIKE CONCAT('ZK_', YEAR(CURDATE()), '_%');
        ";
    
        $result = $this->db->query($sql)->getRow();
    
        // Caso ainda não haja registros para o ano atual, começamos a sequência com 1
        return $result->next_sequence ?: '01';
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
        if (!empty($is_bidding)) {
            if($is_bidding == 0)
            {

                $where .= " AND ($estimates_table.is_bidding=$is_bidding OR $estimates_table.is_bidding IS NULL)";
            }
            else
            {
                $where .= " AND $estimates_table.is_bidding=$is_bidding";
            }
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }
        else
        {
            $where .= " AND (YEAR($estimates_table.estimate_date) = YEAR(CURDATE())) ";
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

        $statuses = $this->_get_clean_value($options, "statuses");
        if ($statuses) {
            $where .= " AND (FIND_IN_SET($estimates_table.status, '$statuses')) ";
        }


        $estrajoin = "";

        $seller_ids = $this->_get_clean_value($options, "seller_ids");
        if ($seller_ids) {
            $where_us = " AND us.id=".$seller_ids." ";
            $estrajoin = " JOIN 
                    crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
                JOIN 
                    crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
                JOIN 
                    crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us";
        }

        $custom_field_filter = $this->_get_clean_value($options, "custom_field_filter");
        if ($custom_field_filter && isset($custom_field_filter["4"])) {
            $where_us = " AND us.id=".$custom_field_filter["4"]." ";
            $estrajoin = " JOIN 
                    crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
                JOIN 
                    crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
                JOIN 
                    crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us";
            unset($options['custom_field_filter']['4']);
        }

        $show_own_estimates_only_user_id = $this->_get_clean_value($options, "show_own_estimates_only_user_id");
        if ($show_own_estimates_only_user_id) {
            $where_us = " AND us.id=".$show_own_estimates_only_user_id." ";
            $estrajoin = " JOIN 
                    crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
                JOIN 
                    crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
                JOIN 
                    crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us";
        }

        $exclude_draft = $this->_get_clean_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $estimates_table.status!='draft' ";
        }

        $clients_only = $this->_get_clean_value($options, "clients_only");
        if ($clients_only) {
            $where .= " AND $estimates_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0)";
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
           $estimate_value_calculation AS estimate_value, 
            (SELECT COUNT(*) 
            FROM $estimates_table AS revisions 
            WHERE revisions.parent_estimate = COALESCE($estimates_table.estimate_number, $estimates_table.estimate_number_temp) AND revisions.deleted = 0
            ) > 0 AS has_revisions, 
            tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
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
        $estrajoin
        WHERE $estimates_table.deleted=0 AND $estimates_table.parent_estimate IS NULL $where $custom_fields_where GROUP BY $estimates_table.id";

        return $this->db->query($sql);
    }

    function get_revisions($options, $estimate_id) {

        $estimates_table = $this->db->prefixTable('estimates');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');
        $estimate_type_table = $this->db->prefixTable('estimate_type');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');
        $items_table = $this->db->prefixTable('items');

        $where = "";

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


        $estrajoin = "";

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("estimates", $custom_fields, $estimates_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT $estimates_table.*, $estimate_type_table.title AS estimate_type, client_contact.id AS contact_id, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name, $projects_table.title as project_title, $clients_table.is_lead,
           CONCAT($users_table.first_name, ' ',$users_table.last_name) AS signer_name, $users_table.email AS signer_email,
           $estimate_value_calculation AS estimate_value,  
            tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
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
        $estrajoin
        WHERE $estimates_table.deleted=0 AND $estimates_table.parent_estimate = (SELECT COALESCE(estimate_number, estimate_number_temp) FROM $estimates_table WHERE id = $estimate_id) $where $custom_fields_where";
       
        return $this->db->query($sql);
    }

    function get_files($estimate_id) {
        $estimates_table = $this->db->prefixTable('estimates');
        $sql = "SELECT $estimates_table.files
        FROM $estimates_table
        WHERE $estimates_table.deleted=0 AND ($estimates_table.estimate_number = (SELECT COALESCE(estimate_number, estimate_number_temp) FROM $estimates_table WHERE id = $estimate_id) OR $estimates_table.estimate_number_temp = (SELECT COALESCE(estimate_number, estimate_number_temp) FROM $estimates_table WHERE id = $estimate_id))";
        
        return $this->db->query($sql);
    }
    
    function get_search_suggestion($search = "", $options = array()) {
        $estimates_table = $this->db->prefixTable('estimates');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        if ($search) {
            $search = $this->db->escapeLikeString($search);
        }

        $sql = "SELECT $estimates_table.id, CONCAT('#', $estimates_table.estimate_number, ' - ', $clients_table.company_name) AS title
        FROM $estimates_table  
        INNER JOIN $clients_table ON $clients_table.id = $estimates_table.client_id
        WHERE $estimates_table.deleted=0 AND ($estimates_table.estimate_number LIKE '%$search%' ESCAPE '!') $where
        ORDER BY $estimates_table.id ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function is_duplicate_code($estimate_number, $id = 0) {

        $result = $this->get_all_regex(array("estimate_number" => $estimate_number), 1000000, 0, $estimate_number);

        if(count($result->getResult()) > 1)
        {
            foreach($result->getResult() as $result)
            {
                if($result->deleted == 0)
                {
                    return $result;
                }
            }
            return false;
        }
        else
        {
            if (count($result->getResult()) && $result->getRow()->id != $id) {
                return $result->getRow();
            } else {
                return false;
            }
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
            $where .= " AND (u.id=$seller OR us.id=$seller)";
        }

        $pos = $this->_get_clean_value($options, "pos");
        if ($pos) {
            $where .= " AND (CASE WHEN us.id IS NOT NULL THEN us.role_id ELSE u.role_id END) IN (10, 12)";
        }
        else
        {
            $where .= " AND (CASE WHEN us.id IS NOT NULL THEN us.role_id ELSE u.role_id END) NOT IN (10, 12)";
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
                        CONCAT(
                            IFNULL(us.id, u.id), '--::--', 
                            IFNULL(us.first_name, u.first_name), ' ', 
                            IFNULL(us.last_name, u.last_name), '--::--', 
                            IFNULL(us.image, COALESCE(u.image, '')), '--::--', 
                            IFNULL(us.user_type, u.user_type)
                        ) AS 'Vendedor',
                    COUNT(DISTINCT e.id) AS 'PropostasEmitidas',
                    SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) AS 'PropostasFechadas',
                    CONCAT(ROUND(SUM(CASE WHEN e.status = 'accepted' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT e.id), 0), 2), '%') AS 'PercentualConversao'
                FROM 
                    crm_estimates e
                JOIN 
                    crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
                JOIN 
                    crm_custom_field_values cfvu ON e.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
                JOIN 
                    crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0
                JOIN 
                    crm_users u ON e.created_by = u.id
                WHERE 
                    e.deleted = 0
                    AND YEAR(e.estimate_date) = YEAR(CURDATE())
                    AND (e.is_bidding = 0 OR e.is_bidding IS NULL)
                    $where 
                GROUP BY 
                    DATE_FORMAT(e.estimate_date, '%Y-%m'), CONCAT(
                            IFNULL(us.id, u.id), '--::--', 
                            IFNULL(us.first_name, u.first_name), ' ', 
                            IFNULL(us.last_name, u.last_name), '--::--', 
                            IFNULL(us.image, COALESCE(u.image, '')), '--::--', 
                            IFNULL(us.user_type, u.user_type)
                        )
            ) AS main_data
        LEFT JOIN 
            (
                SELECT 
                    DATE_FORMAT(e.estimate_date, '%Y-%m') AS 'Mes',
                        CONCAT(
                            IFNULL(us.id, u.id), '--::--', 
                            IFNULL(us.first_name, u.first_name), ' ', 
                            IFNULL(us.last_name, u.last_name), '--::--', 
                            IFNULL(us.image, COALESCE(u.image, '')), '--::--', 
                            IFNULL(us.user_type, u.user_type)
                        ) AS 'Vendedor',
                    SUM(ei.quantity * ei.rate) AS 'ValorEmitido',
                    SUM(CASE WHEN e.status = 'accepted' THEN COALESCE(ei.quantity * ei.rate, cfv.value) ELSE 0 END) AS 'ValorFechado'
                FROM 
                    crm_estimates e
                JOIN 
                    crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
                JOIN 
                    crm_custom_field_values cfvu ON e.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
                JOIN 
                    crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0
                JOIN 
                    crm_users u ON e.created_by = u.id
                LEFT JOIN 
                    crm_estimate_items ei ON e.id = ei.estimate_id
                LEFT JOIN 
                    crm_custom_fields cf ON cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
                LEFT JOIN 
                    crm_custom_field_values cfv ON e.id = cfv.related_to_id AND cfv.custom_field_id = cf.id AND cfv.related_to_type = 'estimates'
                WHERE 
                    e.deleted = 0
                    AND YEAR(e.estimate_date) = YEAR(CURDATE())
                    AND (e.is_bidding = 0 OR e.is_bidding IS NULL)
                    $where
                GROUP BY 
                    DATE_FORMAT(e.estimate_date, '%Y-%m'), CONCAT(
                            IFNULL(us.id, u.id), '--::--', 
                            IFNULL(us.first_name, u.first_name), ' ', 
                            IFNULL(us.last_name, u.last_name), '--::--', 
                            IFNULL(us.image, COALESCE(u.image, '')), '--::--', 
                            IFNULL(us.user_type, u.user_type)
                        )
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
                AND YEAR(e.estimate_date) = YEAR(CURDATE())
                AND (e.is_bidding = 0 OR e.is_bidding IS NULL)
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
                            AND (e.is_bidding = 0 OR e.is_bidding IS NULL)
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
                            SUM(CASE WHEN e.status = 'accepted' THEN COALESCE(ei.quantity * ei.rate, cfv.value) ELSE 0 END) AS 'ValorFechado'
                        FROM 
                            crm_estimates e
                        JOIN 
                            crm_company c ON e.company_id = c.id
                        LEFT JOIN 
                            crm_estimate_items ei ON e.id = ei.estimate_id
                        LEFT JOIN 
                            crm_custom_fields cf ON cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
                        LEFT JOIN 
                            crm_custom_field_values cfv ON e.id = cfv.related_to_id AND cfv.custom_field_id = cf.id AND cfv.related_to_type = 'estimates'
                        WHERE 
                            e.deleted = 0
                            AND (e.is_bidding = 0 OR e.is_bidding IS NULL)
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
    
    function login_user_id() {
        $session = \Config\Services::session();
        return $session->has("user_id") ? $session->get("user_id") : "";
    }

    function user_is_admin() {
        $id = $this->Users_model->login_user_id();
        
        $options = array("status" => "active", "id" => $id);
        $user = $this->Users_model->get_details($options)->getRow();
        return $user->is_admin ? true : false;
    }

    function count_total_emmited_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');

        $where = $where_us = "";

        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }

        $join = "LEFT JOIN";

        if(!$this->user_is_admin())
        {
           $where_us .= " AND us.id=".$this->login_user_id()." ";
           $join = "JOIN";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.estimate_number) AS total
        FROM $estimates_table 
        $join 
            crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
        $join 
            crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
        $join 
            crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us
        WHERE $estimates_table.deleted=0 AND $estimates_table.estimate_number IS NOT NULL AND $estimates_table.status IN ('draft', 'sent', 'accepted', 'rejected') AND ($estimates_table.is_bidding = 0 OR $estimates_table.is_bidding IS NULL) $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_rejected_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');

        $where = $where_us = "";

        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }

        $join = "LEFT JOIN";

        if(!$this->user_is_admin())
        {
            $where_us .= " AND FIND_IN_SET(".$this->login_user_id().",us.id)";
            $join = "JOIN";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        $join 
            crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
        $join 
            crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
        $join 
            crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us
        WHERE $estimates_table.deleted=0 AND $estimates_table.estimate_number IS NOT NULL AND $estimates_table.status IN ('rejected') AND ($estimates_table.is_bidding = 0 OR $estimates_table.is_bidding IS NULL) $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_approved_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');

        $where = $where_us = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }
        
        $join = "LEFT JOIN";

        if(!$this->user_is_admin())
        {
           $where_us .= " AND FIND_IN_SET(".$this->login_user_id().",us.id)";
           $join = "JOIN";
        }

        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table   
        $join  
            crm_custom_fields cfu ON cfu.title = 'Vendedor' AND cfu.related_to = 'estimates'
        $join  
            crm_custom_field_values cfvu ON $estimates_table.id = cfvu.related_to_id AND cfvu.custom_field_id = cfu.id AND cfvu.related_to_type = 'estimates'
        $join  
            crm_users us ON FIND_IN_SET(us.id, cfvu.value) > 0 $where_us
        WHERE $estimates_table.deleted=0 AND $estimates_table.estimate_number IS NOT NULL AND $estimates_table.status = 'accepted' AND ($estimates_table.is_bidding = 0 OR $estimates_table.is_bidding IS NULL) $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_emmited_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');
      
        $where = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.estimate_number IS NOT NULL AND $estimates_table.status <> 'accepted' AND $estimates_table.is_bidding = 1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_approved_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');

        $where = "";
        
        if ($date_start && $date_end) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$date_start' AND '$date_end') ";
        }


        $sql = "SELECT COUNT(DISTINCT $estimates_table.id) AS total
        FROM $estimates_table 
        WHERE $estimates_table.deleted=0 AND $estimates_table.estimate_number IS NOT NULL AND $estimates_table.status = 'accepted' AND $estimates_table.is_bidding = 1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_amount_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $custom_fields_table = $this->db->prefixTable('custom_fields');
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');
        $estimate_value_items_table = $this->db->prefixTable('estimate_value_items');
        
        $where = [];
        $parameters = [];
        
        // Filtrando pelo item
        $item = $this->_get_clean_value($options, "item");
        if ($item) {
            $item_conditions = [];
            foreach ($item as $it) {
                $item_conditions[] = "($estimate_items_table.title LIKE ? OR $estimate_items_table.description LIKE ?)";
                $parameters[] = "%$it%";
                $parameters[] = "%$it%";
            }
            $where[] = "(" . implode(" OR ", $item_conditions) . ")";
        }
        
        // Filtrando pelas datas
        if ($date_start && $date_end) {
            $where[] = "($estimates_table.estimate_date BETWEEN ? AND ?)";
            $parameters[] = $date_start;
            $parameters[] = $date_end;
        }
        
        $where_clause = $where ? " AND " . implode(" AND ", $where) : "";
        // Construindo a consulta SQL
        $sql = "
            SELECT 
                SUM(COALESCE(
                    CASE 
                        WHEN ev.is_checked = 1 THEN 
                            CASE 
                                WHEN ev.currency = 'BRL' THEN ev.converted_amount
                                ELSE ev.amount 
                            END
                        ELSE
                            CASE 
                                WHEN ev.currency = 'BRL' THEN ev.converted_amount
                                ELSE ev.amount 
                            END
                    END, 0)) AS total
            FROM 
                $estimates_table
            LEFT JOIN 
                $estimate_value_items_table ev ON ev.estimate_id = $estimates_table.id
            LEFT JOIN 
                $estimate_items_table ON $estimate_items_table.estimate_id = $estimates_table.id 
                AND $estimate_items_table.deleted = 0
            LEFT JOIN 
                $custom_fields_table cf ON cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
            LEFT JOIN 
                $custom_field_values_table cfv ON $estimates_table.id = cfv.related_to_id 
                AND cfv.custom_field_id = cf.id 
                AND cfv.related_to_type = 'estimates'
            WHERE 
                $estimates_table.deleted = 0 
                AND $estimates_table.status = 'accepted'
                AND $estimates_table.estimate_number IS NOT NULL 
                AND ($estimates_table.is_bidding = 0 OR $estimates_table.is_bidding IS NULL)
                AND YEAR($estimates_table.estimate_date) = YEAR(CURDATE())
                $where_clause;
        ";
        
        // Executando a consulta
        $query = $this->db->query($sql, $parameters);
        $result = $query->getRow();
        
        return $result ? $result->total : 0;
    }
    
    
    function count_total_amount_bidding_estimates($options = array(), $date_start = null, $date_end = null) {
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $custom_fields_table = $this->db->prefixTable('custom_fields');
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');
        $estimate_value_items_table = $this->db->prefixTable('estimate_value_items');
        
        $where = [];
        $parameters = [];
        
        // Filtrando pelo item
        $item = $this->_get_clean_value($options, "item");
        if ($item) {
            $item_conditions = [];
            foreach ($item as $it) {
                $item_conditions[] = "($estimate_items_table.title LIKE ? OR $estimate_items_table.description LIKE ?)";
                $parameters[] = "%$it%";
                $parameters[] = "%$it%";
            }
            $where[] = "(" . implode(" OR ", $item_conditions) . ")";
        }
        
        // Filtrando pelas datas
        if ($date_start && $date_end) {
            $where[] = "($estimates_table.estimate_date BETWEEN ? AND ?)";
            $parameters[] = $date_start;
            $parameters[] = $date_end;
        }
        
        $where_clause = $where ? " AND " . implode(" AND ", $where) : "";
        
        // Construindo a consulta SQL
        $sql = "
            SELECT 
                SUM(COALESCE(
                    CASE 
                        WHEN ev.is_checked = 1 THEN 
                            CASE 
                                WHEN ev.currency = 'BRL' THEN ev.converted_amount
                                ELSE ev.amount 
                            END
                        ELSE
                            CASE 
                                WHEN ev.currency = 'BRL' THEN ev.converted_amount
                                ELSE ev.amount 
                            END
                    END, 0)) AS total
            FROM 
                $estimates_table
            LEFT JOIN 
                $estimate_value_items_table ev ON ev.estimate_id = $estimates_table.id
            LEFT JOIN 
                $estimate_items_table ON $estimate_items_table.estimate_id = $estimates_table.id 
                AND $estimate_items_table.deleted = 0
            LEFT JOIN 
                $custom_fields_table cf ON cf.title = 'Valor Estimado' AND cf.related_to = 'estimates'
            LEFT JOIN 
                $custom_field_values_table cfv ON $estimates_table.id = cfv.related_to_id 
                AND cfv.custom_field_id = cf.id 
                AND cfv.related_to_type = 'estimates'
            WHERE 
                $estimates_table.deleted = 0 
                AND $estimates_table.status = 'accepted'
                AND $estimates_table.estimate_number IS NOT NULL 
                AND ($estimates_table.is_bidding = 1)
                AND YEAR($estimates_table.estimate_date) = YEAR(CURDATE())
                $where_clause;
        ";
        
        // Executando a consulta
        $query = $this->db->query($sql, $parameters);
        $result = $query->getRow();
        
        return $result ? $result->total : 0;
    }
}
