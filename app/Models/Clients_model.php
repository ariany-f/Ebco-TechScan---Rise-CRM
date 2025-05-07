<?php

namespace App\Models;

class Clients_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'clients';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $clients_status_table = $this->db->prefixTable('client_status');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $client_groups_table = $this->db->prefixTable('client_groups');
        $invoice_rules_table = $this->db->prefixTable('invoice_rules');
        $lead_status_table = $this->db->prefixTable('lead_status');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $tickets_table = $this->db->prefixTable('tickets');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $clients_table.id=$id";
        }

        $custom_field_type = "clients";

        $leads_only = $this->_get_clean_value($options, "leads_only");
        if ($leads_only) {
            $custom_field_type = "leads";
            $where .= " AND $clients_table.is_lead=1";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $clients_table.lead_status_id='$status'";
        }

        $source = $this->_get_clean_value($options, "source");
        if ($source) {
            $where .= " AND $clients_table.lead_source_id='$source'";
        }

        $owner_id = $this->_get_clean_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND $clients_table.owner_id=$owner_id";
        }

        $created_by = $this->_get_clean_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $clients_table.created_by=$created_by";
        }

        $setor = $this->_get_clean_value($options, "setor");
        if ($setor) {
            $where .= " AND $clients_table.setor='$setor'";
        }

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND ($clients_table.created_by=$show_own_clients_only_user_id OR $clients_table.owner_id=$show_own_clients_only_user_id)";
        }

        if (!$id && !$leads_only) {
            //only clients
            $where .= " AND $clients_table.is_lead=0";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where .= " AND FIND_IN_SET('$group_id', $clients_table.group_ids)";
        }


        $status_ids = $this->_get_clean_value($options, "status_ids");
        if ($status_ids) {
            $where .= " AND FIND_IN_SET($clients_table.status_id,'$status_ids')";
        }
        
        $invoice_rule_id = $this->_get_clean_value($options, "invoice_rule_id");
        if ($invoice_rule_id) {
            $where .= " AND FIND_IN_SET('$invoice_rule_id', $clients_table.invoice_rule_id)";
        }

        $quick_filter = $this->_get_clean_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table);
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        if ($start_date) {
            $where .= " AND DATE($clients_table.created_date)>='$start_date'";
        }
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($end_date) {
            $where .= " AND DATE($clients_table.created_date)<='$end_date'";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        
        $invoice_rules = $this->_get_clean_value($options, "invoice_rules");
        $where .= $this->prepare_allowed_invoice_rules_query($clients_table, $invoice_rules);

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string($custom_field_type, $custom_fields, $clients_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $invoice_value_calculation_query = "(SUM" . $this->_get_invoice_value_calculation_query($invoices_table) . ")";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $invoice_value_select = "IFNULL(invoice_details.invoice_value,0)";
        $payment_value_select = "IFNULL(invoice_details.payment_received,0)";

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }


        $available_order_by_list = array(
            "id" => $clients_table . ".id",
            "company_name" => $clients_table . ".company_name",
            "created_date" => $clients_table . ".created_date",
            "primary_contact" => $users_table . ".first_name",
            "status" => "lead_status_title",
            "owner_name" => "owner_details.owner_name",
            "primary_contact" => "primary_contact",
            "client_groups" => "client_groups",
            "invoice_rules" => "invoice_rules"
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        $order = "";

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }


        $search_by = get_array_value($options, "search_by");
        if ($search_by) {
            $search_by = $this->db->escapeLikeString($search_by);

            $where .= " AND (";
            $where .= " $clients_table.id LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.company_name LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.cnpj LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.matriz_cnpj LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR CONCAT($users_table.first_name, ' ', $users_table.last_name) LIKE '%$search_by%' ESCAPE '!' ";

            if ($leads_only) {
                $where .= " OR owner_details.owner_name LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $lead_status_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= $this->get_custom_field_search_query($clients_table, "leads", $search_by);
            } else {
                $where .= $this->get_custom_field_search_query($clients_table, "clients", $search_by);
            }

            $where .= " )";
        }


        $sql = "SELECT SQL_CALC_FOUND_ROWS $clients_table.*, 
        $clients_status_table.title AS status_title,  
        $clients_status_table.color AS status_color,
        CONCAT($users_table.first_name, ' ', $users_table.last_name) AS primary_contact, 
        $users_table.email AS primary_contact_email, 
        $users_table.id AS primary_contact_id, 
        $users_table.image AS contact_avatar,  
        project_table.total_projects, 
        $payment_value_select AS payment_received 
        $select_custom_fieds,
        IF((($invoice_value_select > $payment_value_select) AND ($invoice_value_select - $payment_value_select) <0.05), $payment_value_select, $invoice_value_select) AS invoice_value,
        (SELECT GROUP_CONCAT($invoice_rules_table.title) 
         FROM $invoice_rules_table 
         WHERE FIND_IN_SET($invoice_rules_table.id, $clients_table.invoice_rule_id)) AS invoice_rule_id,
        (SELECT GROUP_CONCAT($client_groups_table.title) 
         FROM $client_groups_table 
         WHERE FIND_IN_SET($client_groups_table.id, $clients_table.group_ids)) AS client_groups, 
        $lead_status_table.title AS lead_status_title,  
        $lead_status_table.color AS lead_status_color,
        owner_details.owner_name, 
        owner_details.owner_avatar,
        estimates_table.total_estimates_sent,
        estimates_table.total_estimates_approved
        FROM $clients_table
        LEFT JOIN $clients_status_table ON $clients_table.status_id = $clients_status_table.id 
        LEFT JOIN $users_table ON $users_table.client_id = $clients_table.id AND $users_table.deleted=0 AND $users_table.is_primary_contact=1 
        LEFT JOIN (SELECT client_id, COUNT(id) AS total_projects 
                FROM $projects_table 
                WHERE deleted=0 AND project_type='client_project' 
                GROUP BY client_id) AS project_table ON project_table.client_id= $clients_table.id
        LEFT JOIN (SELECT client_id, SUM(payments_table.payment_received) as payment_received, 
                        $invoice_value_calculation_query as invoice_value 
                FROM $invoices_table
                LEFT JOIN (SELECT $taxes_table.* 
                            FROM $taxes_table) AS tax_table ON tax_table.id = $invoices_table.tax_id
                LEFT JOIN (SELECT $taxes_table.* 
                            FROM $taxes_table) AS tax_table2 ON tax_table2.id = $invoices_table.tax_id2 
                LEFT JOIN (SELECT $taxes_table.* 
                            FROM $taxes_table) AS tax_table3 ON tax_table3.id = $invoices_table.tax_id3 
                LEFT JOIN (SELECT invoice_id, SUM(amount) AS payment_received 
                            FROM $invoice_payments_table 
                            WHERE deleted=0 
                            GROUP BY invoice_id) AS payments_table ON payments_table.invoice_id=$invoices_table.id AND $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                LEFT JOIN (SELECT invoice_id, SUM(total) AS invoice_value 
                            FROM $invoice_items_table 
                            WHERE deleted=0 
                            GROUP BY invoice_id) AS items_table ON items_table.invoice_id=$invoices_table.id AND $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                WHERE $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                GROUP BY $invoices_table.client_id    
                ) AS invoice_details ON invoice_details.client_id= $clients_table.id 
        LEFT JOIN $lead_status_table ON $clients_table.lead_status_id = $lead_status_table.id 
        LEFT JOIN (SELECT $users_table.id, 
                        CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name, 
                        $users_table.image AS owner_avatar 
                FROM $users_table 
                WHERE $users_table.deleted=0 AND $users_table.user_type='staff') AS owner_details ON owner_details.id=$clients_table.owner_id
        LEFT JOIN (SELECT client_id, 
                        COUNT(CASE WHEN status='sent' OR status='accepted' THEN 1 END) AS total_estimates_sent, 
                        COUNT(CASE WHEN status='accepted' THEN 1 END) AS total_estimates_approved 
                FROM $estimates_table 
                WHERE deleted=0 
                GROUP BY client_id) AS estimates_table ON estimates_table.client_id = $clients_table.id
        $join_custom_fieds               
        WHERE $clients_table.deleted = 0 AND $clients_table.status_id <> 2 $where $custom_fields_where  
        $order $limit_offset";
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
   
    function new_clients($options = array()) {

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $where = "";
        $where_client = "";
        $seller = $this->_get_clean_value($options, "seller_id");
        if ($seller) {
            $where .= " AND u.id=$seller";
        }
        
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where_client .= " AND (c.created_date BETWEEN '$start_date' AND '$end_date' AND STR_TO_DATE(c.client_migration_date, '%Y-%m-%d') IS NULL) OR (STR_TO_DATE(c.client_migration_date, '%Y-%m-%d') IS NOT NULL AND c.client_migration_date BETWEEN '$start_date' AND '$end_date')";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            'csi.Mes',
            'csi.seller_name'
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $sql = "WITH client_status AS (
            SELECT
                c.id,
                c.owner_id,
                IF(STR_TO_DATE(c.client_migration_date, '%Y-%m-%d') IS NULL, c.created_date, c.client_migration_date) AS client_date,
                'Client' AS current_status
            FROM
                crm_clients c
            WHERE
                c.is_lead = 0 AND c.deleted = 0 AND c.status_id <> 2 $where_client
        ),
        clients_seller_info AS (
            SELECT
                cs.client_date,
                u.id AS user_id,
                CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type) AS seller_name
            FROM
                client_status cs
            LEFT JOIN
                crm_users u ON u.id = cs.owner_id
            WHERE
                1=1 $where
        )
        SELECT
            DATE_FORMAT(csi.client_date, '%M') AS Mes,
            csi.seller_name AS Vendedor,
            COUNT(*) AS new_clients
        FROM
            clients_seller_info csi
        GROUP BY
            DATE_FORMAT(csi.client_date, '%M'),
            DATE_FORMAT(csi.client_date, '%Y'),
            csi.seller_name
        $order $limit_offset;";

        $raw_query = $this->db->query($sql);
        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        $results = $raw_query->getResult();
            
        // Loop through results to fetch visit counts for each seller
        foreach ($results as $i => $result) {
            $seller_name = $result->Vendedor;
            $visit_count = $this->get_visit_count_for_seller($seller_name, $start_date, $end_date);
            $results[$i]->visit_count = $visit_count;
        }

        if ($limit) {
            return array(
                "data" => $results,
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    function get_visit_count_for_seller($seller_name, $start_date, $end_date) {
        // Implement logic to fetch visit count for a seller within given date range
        $where_visit = "";
    
        if ($start_date && $end_date) {
            $where_visit .= " AND crm_events.start_date BETWEEN '$start_date' AND '$end_date' ";
        }
    
        $collaborator_parts = explode("--::--", $seller_name);
        $user_id = get_array_value($collaborator_parts, 0);
    
        // Query to get visit count for the seller in crm_events table
        $sql_visit_count = "
            SELECT COUNT(*) AS visit_count 
            FROM crm_events 
            WHERE (created_by = ? OR FIND_IN_SET(CONCAT('member:', ?), share_with) OR FIND_IN_SET(share_with, 'all'))
                AND FIND_IN_SET('1', labels) > 0
                AND deleted = 0
              $where_visit
        ";
    
        $query_visit_count = $this->db->query($sql_visit_count, [$user_id, $user_id]);
        $result_visit_count = $query_visit_count->getRow();
    
        return $result_visit_count ? $result_visit_count->visit_count : 0;
    }

    function leads_prospects($options = array()) {

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $where = "";
        $seller = $this->_get_clean_value($options, "seller_id");
        if ($seller) {
            $where .= " AND cws.owner_id=$seller";
        }
        
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND (( cws.client_migration_date BETWEEN '$start_date' AND '$end_date') OR (cws.created_date BETWEEN '$start_date' AND '$end_date'))";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            'csi.seller_name',
            'csi.client_migration_date'
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }
        
        $sql = "WITH lead_status AS (
            SELECT
                ls.id AS lead_status_id,
                ls.title AS lead_status_title
            FROM
                crm_lead_status ls
        ),
        clients_with_status AS (
            SELECT
                c.id,
                c.is_lead,
                c.last_lead_status,
                c.created_date,
                c.client_migration_date,
                c.owner_id,
                CASE
                    WHEN c.is_lead = 1 AND s.lead_status_title LIKE 'Prospect%' THEN 'Prospect'
                    WHEN c.is_lead = 1 THEN 'Lead'
                    ELSE 'Client'
                END AS current_status,
                s.lead_status_title AS current_status_title
            FROM
                crm_clients c
            LEFT JOIN
                lead_status s ON c.lead_status_id = s.lead_status_id
        ),
        prospection_sellers AS (
            SELECT
                u.id AS user_id,
                CONCAT(u.id, '--::--', u.first_name, ' ', u.last_name, '--::--', COALESCE(u.image, ''), '--::--', u.user_type) AS seller_name
            FROM
                crm_users u
        ),
        clients_seller_info AS (
            SELECT
                cws.id,
                cws.current_status,
                cws.current_status_title,
                cws.client_migration_date,
                cws.created_date,
                cws.owner_id,
                ps.user_id,
                ps.seller_name,
                CONCAT( CASE WHEN EXTRACT(MONTH FROM cws.client_migration_date) = 0 THEN EXTRACT(MONTH FROM cws.created_date) ELSE EXTRACT(MONTH FROM cws.client_migration_date) END, '/', CASE WHEN EXTRACT(YEAR FROM cws.client_migration_date) = 0 THEN EXTRACT(YEAR FROM cws.created_date) ELSE EXTRACT(YEAR FROM cws.client_migration_date) END) AS migration_date
            FROM
                clients_with_status cws
            LEFT JOIN
                prospection_sellers ps ON cws.owner_id = ps.user_id
            WHERE
                1=1 $where
        )
        SELECT
            DATE_FORMAT(STR_TO_DATE(csi.migration_date, '%m/%Y'), '%M') AS 'Mes',
            csi.seller_name as 'Vendedor',
            COUNT(CASE WHEN csi.current_status = 'Lead' THEN 1 END) AS new_leads,
            COUNT(CASE WHEN csi.current_status = 'Prospect' AND csi.client_migration_date IS NOT NULL THEN 1 END) AS new_prospects,
            CONCAT(
                ROUND(
                    CASE 
                        WHEN COUNT(CASE WHEN csi.current_status = 'Lead' THEN 1 END) = 0 THEN 0
                        ELSE COUNT(CASE WHEN csi.current_status = 'Prospect' AND csi.client_migration_date IS NOT NULL THEN 1 END) * 100.0 / COUNT(CASE WHEN csi.current_status = 'Lead' THEN 1 END)
                    END, 2
                ), '%'
            ) AS 'Conversao'
        FROM
            clients_seller_info csi
        WHERE
            csi.seller_name IS NOT NULL
        GROUP BY
            csi.migration_date, csi.seller_name
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

    private function make_quick_filter_query($filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table) {
        $query = "";

        if ($filter == "has_open_projects" || $filter == "has_completed_projects" || $filter == "has_any_hold_projects" || $filter == "has_canceled_projects" || $filter == "has_new_projects") {
            $status = "open_project";
            if ($filter == "has_completed_projects") {
                $status = "completed_project";
            } else if ($filter == "has_any_hold_projects") {
                $status = "hold_project";
            } else if ($filter == "has_new_projects") {
                $status = "new_project";
            } else if ($filter == "has_canceled_projects") {
                $status = "canceled_project";
            }

            $query = " AND $clients_table.id IN(SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.deleted=0 AND $projects_table.project_type='client_project' AND $projects_table.status='$status') ";
        } else if ($filter == "has_unpaid_invoices" || $filter == "has_overdue_invoices" || $filter == "has_partially_paid_invoices") {
            $now = get_my_local_time("Y-m-d");
            $invoice_value_calculation_query = $this->_get_invoice_value_calculation_query($invoices_table);
            $invoice_value_calculation = "TRUNCATE($invoice_value_calculation_query,2)";

            $invoice_where = " AND $invoices_table.status !='draft' AND $invoices_table.status!='cancelled' AND IFNULL(payments_table.payment_received,0)<=0";
            if ($filter == "has_overdue_invoices") {
                $invoice_where = " AND $invoices_table.status !='draft' AND $invoices_table.status!='cancelled' AND $invoices_table.due_date<'$now' AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)<$invoice_value_calculation";
            } else if ($filter == "has_partially_paid_invoices") {
                $invoice_where = " AND IFNULL(payments_table.payment_received,0)>0 AND IFNULL(payments_table.payment_received,0)<$invoice_value_calculation";
            }

            $query = " AND $clients_table.id IN(
                            SELECT $invoices_table.client_id FROM $invoices_table 
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $invoices_table.tax_id
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $invoices_table.tax_id2
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table3 ON tax_table3.id = $invoices_table.tax_id3
                            LEFT JOIN (SELECT invoice_id, SUM(amount) AS payment_received FROM $invoice_payments_table WHERE deleted=0 GROUP BY invoice_id) AS payments_table ON payments_table.invoice_id = $invoices_table.id 
                            LEFT JOIN (SELECT invoice_id, SUM(total) AS invoice_value FROM $invoice_items_table WHERE deleted=0 GROUP BY invoice_id) AS items_table ON items_table.invoice_id = $invoices_table.id 
                            WHERE $invoices_table.deleted=0 $invoice_where
                    ) ";
        } else if ($filter == "has_open_estimates" || $filter == "has_accepted_estimates") {
            $status = "sent";
            if ($filter == "has_accepted_estimates") {
                $status = "accepted";
            }

            $query = " AND $clients_table.id IN(SELECT $estimates_table.client_id FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.status='$status') ";
        } else if ($filter == "has_new_estimate_requests" || $filter == "has_estimate_requests_in_progress") {
            $status = "new";
            if ($filter == "has_estimate_requests_in_progress") {
                $status = "processing";
            }

            $query = " AND $clients_table.id IN(SELECT $estimate_requests_table.client_id FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.status='$status') ";
        } else if ($filter == "has_open_tickets") {
            $query = " AND $clients_table.id IN(SELECT $tickets_table.client_id FROM $tickets_table WHERE $tickets_table.deleted=0 AND $tickets_table.status!='closed') ";
        } else if ($filter == "has_new_orders") {
            $query = " AND $clients_table.id IN(SELECT $orders_table.client_id FROM $orders_table WHERE $orders_table.deleted=0 AND $orders_table.status_id='1') ";
        } else if ($filter == "has_open_proposals" || $filter == "has_accepted_proposals" || $filter == "has_rejected_proposals") {
            $status = "sent";
            if ($filter == "has_accepted_proposals") {
                $status = "accepted";
            } else if ($filter == "has_rejected_proposals") {
                $status = "declined";
            }

            $query = " AND $clients_table.id IN(SELECT $proposals_table.client_id FROM $proposals_table WHERE $proposals_table.deleted=0 AND $proposals_table.status='$status') ";
        }

        return $query;
    }

    function get_primary_contact($client_id = 0, $info = false) {
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT $users_table.id, $users_table.email, $users_table.first_name, $users_table.last_name
        FROM $users_table
        WHERE $users_table.deleted=0 AND $users_table.client_id=$client_id AND $users_table.is_primary_contact=1";
        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            if ($info) {
                return $result->getRow();
            } else {
                return $result->getRow()->id;
            }
        }
    }

    function add_remove_star($client_id, $user_id, $type = "add") {
        $clients_table = $this->db->prefixTable('clients');
        $client_id = $client_id ? $this->db->escapeString($client_id) : $client_id;

        $action = " CONCAT($clients_table.starred_by,',',':$user_id:') ";
        $where = " AND FIND_IN_SET(':$user_id:',$clients_table.starred_by) = 0"; //don't add duplicate

        if ($type != "add") {
            $action = " REPLACE($clients_table.starred_by, ',:$user_id:', '') ";
            $where = "";
        }

        $sql = "UPDATE $clients_table SET $clients_table.starred_by = $action
        WHERE $clients_table.id=$client_id $where";
        return $this->db->query($sql);
    }

    function get_starred_clients($user_id, $client_groups = "") {
        $clients_table = $this->db->prefixTable('clients');

        $where = $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT $clients_table.id,  $clients_table.company_name
        FROM $clients_table
        WHERE $clients_table.deleted=0 AND FIND_IN_SET(':$user_id:',$clients_table.starred_by) $where
        ORDER BY $clients_table.company_name ASC";
        return $this->db->query($sql);
    }

    function delete_client_and_sub_items($client_id) {
        $clients_table = $this->db->prefixTable('clients');
        $general_files_table = $this->db->prefixTable('general_files');
        $users_table = $this->db->prefixTable('users');

        //get client files info to delete the files from directory 
        $client_files_sql = "SELECT * FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$client_id; ";
        $client_files = $this->db->query($client_files_sql)->getResult();

        //delete the client and sub items
        //delete client
        $delete_client_sql = "UPDATE $clients_table SET $clients_table.status_id=2 WHERE $clients_table.id=$client_id; ";
        $this->db->query($delete_client_sql);

        //delete contacts
        // $delete_contacts_sql = "UPDATE $users_table SET $users_table.deleted=1 WHERE $users_table.client_id=$client_id; ";
        // $this->db->query($delete_contacts_sql);

        //delete the project files from directory
        $file_path = get_general_file_path("client", $client_id);
        foreach ($client_files as $file) {
            delete_app_files($file_path, array(make_array_of_file($file)));
        }

        return true;
    }

    function is_duplicate_company_name($company_name, $id = 0) {

        $result = $this->get_all_where(array("company_name" => $company_name, "is_lead" => 0, "deleted" => 0));
        if (count($result->getResult()) && $result->getRow()->id != $id) {
            return $result->getRow();
        } else {
            return false;
        }
    }

    function is_duplicate_cnpj($cnpj, $id = 0) {

        $result = $this->get_all_where(array("REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', ''), ' ', '')" => preg_replace('/\D/', '', $cnpj), "deleted" => 0));
        if (count($result->getResult()) && $result->getRow()->id != $id) {
            return $result->getRow();
        } else {
            return false;
        }
    }

    function get_leads_kanban_details($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $lead_source_table = $this->db->prefixTable('lead_source');
        $users_table = $this->db->prefixTable('users');
        $events_table = $this->db->prefixTable('events');
        $notes_table = $this->db->prefixTable('notes');
        $estimates_table = $this->db->prefixTable('estimates');
        $general_files_table = $this->db->prefixTable('general_files');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');

        $where = "";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $clients_table.lead_status_id='$status'";
        }

        $owner_id = $this->_get_clean_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND $clients_table.owner_id='$owner_id'";
        }

        $source = $this->_get_clean_value($options, "source");
        if ($source) {
            $where .= " AND $clients_table.lead_source_id='$source'";
        }

        $search = get_array_value($options, "search");
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where .= " AND $clients_table.company_name LIKE '%$search%' ESCAPE '!'";
        }

        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("leads", "", $clients_table, $custom_field_filter);
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $users_where = "$users_table.client_id=$clients_table.id AND $users_table.deleted=0 AND $users_table.user_type='lead'";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT $clients_table.id, $clients_table.company_name, $clients_table.sort, IF($clients_table.sort!=0, $clients_table.sort, $clients_table.id) AS new_sort, $clients_table.lead_status_id, $clients_table.owner_id,
                (SELECT $users_table.image FROM $users_table WHERE $users_where AND $users_table.is_primary_contact=1) AS primary_contact_avatar,
                (SELECT COUNT($users_table.id) FROM $users_table WHERE $users_where) AS total_contacts_count,
                (SELECT COUNT($events_table.id) FROM $events_table WHERE $events_table.deleted=0 AND $events_table.client_id=$clients_table.id) AS total_events_count,
                (SELECT COUNT($notes_table.id) FROM $notes_table WHERE $notes_table.deleted=0 AND $notes_table.client_id=$clients_table.id) AS total_notes_count,
                (SELECT COUNT($estimates_table.id) FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.client_id=$clients_table.id) AS total_estimates_count,
                (SELECT COUNT($general_files_table.id) FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$clients_table.id) AS total_files_count,
                (SELECT COUNT($estimate_requests_table.id) FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.client_id=$clients_table.id) AS total_estimate_requests_count,
                $lead_source_table.title AS lead_source_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name
        FROM $clients_table 
        LEFT JOIN $lead_source_table ON $clients_table.lead_source_id = $lead_source_table.id 
        LEFT JOIN $users_table ON $users_table.id = $clients_table.owner_id AND $users_table.deleted=0 AND $users_table.user_type='staff' 
        WHERE $clients_table.deleted=0 AND $clients_table.status_id <> 2 AND $clients_table.is_lead=1 $where $custom_fields_where
        ORDER BY new_sort ASC";

        return $this->db->query($sql);
    }

    function get_proposals_kanban_details($options = array()) {
       
        $proposals_table = $this->db->prefixTable('proposals');
        
        // $users_table = $this->db->prefixTable('users');
        // $events_table = $this->db->prefixTable('events');
        // $notes_table = $this->db->prefixTable('notes');
        // $estimates_table = $this->db->prefixTable('estimates');
        // $general_files_table = $this->db->prefixTable('general_files');
        // $estimate_requests_table = $this->db->prefixTable('estimate_requests');

        $where = "";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $proposals_table.status='$status'";
        }

        // $search = get_array_value($options, "search");
        // if ($search) {
        //     $search = $this->db->escapeLikeString($search);
        //     $where .= " AND $clients_table.company_name LIKE '%$search%' ESCAPE '!'";
        // }

        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("proposals", "", $proposals_table, $custom_field_filter);
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        //$users_where = "$users_table.client_id=$clients_table.id AND $users_table.deleted=0 AND $users_table.user_type='lead'";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT $proposals_table.id, $proposals_table.status,
                -- (SELECT $users_table.image FROM $users_table WHERE $users_where AND $users_table.is_primary_contact=1) AS primary_contact_avatar,
                -- (SELECT COUNT($users_table.id) FROM $users_table WHERE $users_where) AS total_contacts_count,
                -- (SELECT COUNT($events_table.id) FROM $events_table WHERE $events_table.deleted=0 AND $events_table.client_id=$clients_table.id) AS total_events_count,
                -- (SELECT COUNT($notes_table.id) FROM $notes_table WHERE $notes_table.deleted=0 AND $notes_table.client_id=$clients_table.id) AS total_notes_count,
                -- (SELECT COUNT($estimates_table.id) FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.client_id=$clients_table.id) AS total_estimates_count,
                -- (SELECT COUNT($general_files_table.id) FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$clients_table.id) AS total_files_count,
                -- (SELECT COUNT($estimate_requests_table.id) FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.client_id=$clients_table.id) AS total_estimate_requests_count,
                -- $lead_source_table.title AS lead_source_title,
                -- CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name
        FROM $proposals_table 
        -- LEFT JOIN $lead_source_table ON $clients_table.lead_source_id = $lead_source_table.id 
        -- LEFT JOIN $users_table ON $users_table.id = $clients_table.owner_id AND $users_table.deleted=0 AND $users_table.user_type='staff' 
        WHERE $where $custom_fields_where
        ORDER BY id ASC";

        return $this->db->query($sql);
    }

    function get_search_suggestion($search = "", $options = array()) {
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND ($clients_table.created_by=$show_own_clients_only_user_id OR $clients_table.owner_id=$show_own_clients_only_user_id)";
        }

        if ($search) {
            $search = $this->db->escapeLikeString($search);
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        
        $is_lead = $this->_get_clean_value($options, "is_lead");
        if($is_lead)
        {
            $where .= " AND $clients_table.is_lead = $is_lead";
        }

        $sql = "SELECT $clients_table.id, CONCAT($clients_table.company_name, ' - ', $clients_table.cnpj) AS title
        FROM $clients_table  
        WHERE $clients_table.deleted=0 AND ($clients_table.company_name LIKE '%$search%' OR  REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', ''), ' ', '') LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE('$search', '.', ''), '-', ''), '/', ''), ' ', ''), '%') OR  REPLACE(REPLACE(REPLACE(REPLACE(matriz_cnpj, '.', ''), '-', ''), '/', ''), ' ', '') LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE('$search', '.', ''), '-', ''), '/', ''), ' ', ''), '%') ESCAPE '!') $where
        ORDER BY $clients_table.company_name ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function count_total_clients($options = array(), $date_start = null, $date_end = null) {
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

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND $clients_table.created_by=$show_own_clients_only_user_id";
        }

        $filter = $this->_get_clean_value($options, "filter");
        if ($filter) {
            $where .= $this->make_quick_filter_query($filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table);
        }
        
        if($date_start and $date_end)
        {
            $where .= " AND ($clients_table.client_migration_date BETWEEN '$date_start' and '$date_end' OR $clients_table.created_date BETWEEN '$date_start' and '$date_end')";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT COUNT(DISTINCT $clients_table.id) AS total
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 AND $clients_table.status_id <> 2 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_total_prospects($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $lead_status_table = $this->db->prefixTable('lead_status');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND $clients_table.created_by=$show_own_clients_only_user_id";
        }

        $filter = $this->_get_clean_value($options, "filter");
        if ($filter) {
            $where .= $this->make_quick_filter_query($filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table);
        }
        
        if($date_start and $date_end)
        {
            $where .= " AND ($clients_table.client_migration_date BETWEEN '$date_start' and '$date_end' OR $clients_table.created_date BETWEEN '$date_start' and '$date_end')";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT COUNT(DISTINCT $clients_table.id) AS total
        FROM $clients_table 
        INNER JOIN $lead_status_table ON $clients_table.lead_status_id = $lead_status_table.id AND crm_lead_status.deleted = 0
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead = 1 AND $clients_table.status_id <> 2 AND $clients_table.lead_status_id IN (2, 3, 4, 6, 7) $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function get_conversion_rate_with_currency_symbol() {
        $clients_table = $this->db->prefixTable('clients');

        $sql = "SELECT $clients_table.currency_symbol, $clients_table.currency
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.currency!='' AND $clients_table.currency IS NOT NULL
        GROUP BY $clients_table.currency";
        return $this->db->query($sql);
    }

    function get_conversion_leads_prospects($options = array(), $date_start, $date_end) {

        $clients_table = $this->db->prefixTable('clients');
        $lead_status_table = $this->db->prefixTable('lead_status');
            
        // Inicializa a parte da cláusula WHERE
        $whereClauses = ["1=1"];
        $count = "COUNT(CASE WHEN (is_lead = 1 AND $lead_status_table.title LIKE 'Prospect%') OR (is_lead = 0 AND STR_TO_DATE(client_migration_date, '%Y-%m-%d') IS NOT NULL) THEN $clients_table.id END)";

        // Adiciona os filtros de data se os parâmetros forem fornecidos
        if ($date_end && $date_end != '') {
            $whereClauses[] = "created_date <= '$date_end'";
            $count  = "COUNT(CASE 
                            WHEN '$date_start' IS NOT NULL AND '$date_end' IS NOT NULL THEN 
                            CASE WHEN client_migration_date > '$date_end' OR ((is_lead = 1 AND $lead_status_table.title LIKE 'Prospect%') OR (is_lead = 0 AND STR_TO_DATE(client_migration_date, '%Y-%m-%d') IS NOT NULL)) THEN $clients_table.id END
                        END)";
        }

        $whereClauses[] = "YEAR($clients_table.created_date) = YEAR(CURDATE())";

        // Junta as cláusulas WHERE
        $whereSql = implode(' AND ', $whereClauses);

        // Prepara a query
        $sql = "
            SELECT 
                ROUND(
                    CASE 
                        WHEN total_leads = 0 THEN 0
                        ELSE (converted_prospects * 100.0) / total_leads
                    END, 2
                ) AS PercentualConversao
            FROM (
                SELECT 
                    $count AS converted_prospects,
                    COUNT($clients_table.id) AS total_leads
                FROM $clients_table
                LEFT JOIN $lead_status_table ON $lead_status_table.id = $clients_table.status_id
                WHERE $whereSql
            ) AS conversion_data
        ";

        $result = $this->db->query($sql)->getRow();

        return $result ? $result->PercentualConversao : 0;
    }

    function get_conversion_prospects_clients($options = array(), $date_start, $date_end) {

        $clients_table = $this->db->prefixTable('clients');
            
        // Inicializa a parte da cláusula WHERE
        $whereClauses = ["1=1"];
        $count = "COUNT(CASE WHEN is_lead = 0 AND STR_TO_DATE(client_migration_date, '%Y-%m-%d') IS NOT NULL THEN id END)";

        // Adiciona os filtros de data se os parâmetros forem fornecidos
        if ($date_end && $date_end != '') {
            $whereClauses[] = "created_date <= '$date_end'";
            $count  = "COUNT(CASE 
                            WHEN '$date_start' IS NOT NULL AND '$date_end' IS NOT NULL THEN 
                                CASE WHEN is_lead = 0 AND client_migration_date BETWEEN '$date_start' AND '$date_end' THEN id END
                        END)";
        }

        $whereClauses[] = "YEAR($clients_table.created_date) = YEAR(CURDATE())";

        // Junta as cláusulas WHERE
        $whereSql = implode(' AND ', $whereClauses);

        // Prepara a query
        $sql = "
            SELECT 
                ROUND(
                    CASE 
                        WHEN total_prospects = 0 THEN 0
                        ELSE (converted_clients * 100.0) / total_prospects
                    END, 2
                ) AS PercentualConversao
            FROM (
                SELECT 
                    $count AS converted_clients,
                    COUNT(id) AS total_prospects
                FROM $clients_table
                WHERE $whereSql
            ) AS conversion_data
        ";

        $result = $this->db->query($sql)->getRow();

        return $result ? $result->PercentualConversao : 0;
    }

    function count_total_leads($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $show_own_leads_only_user_id = $this->_get_clean_value($options, "show_own_leads_only_user_id");
        if ($show_own_leads_only_user_id) {
            $where .= " AND $clients_table.owner_id=$show_own_leads_only_user_id";
        }
        
        if($date_start and $date_end)
        {
            $where .= " AND ($clients_table.client_migration_date BETWEEN '$date_start' and '$date_end' OR $clients_table.created_date BETWEEN '$date_start' and '$date_end')";
        }

        $sql = "SELECT COUNT($clients_table.id) AS total
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.status_id <> 2 AND $clients_table.lead_status_id=1 AND $clients_table.is_lead=1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function get_lead_statistics($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $lead_status_table = $this->db->prefixTable('lead_status');
        $projects_table = $this->db->prefixTable('projects');

        try {
            $this->db->query("SET sql_mode = ''");
        } catch (\Exception $e) {
            
        }
        $where = "";

        $show_own_leads_only_user_id = $this->_get_clean_value($options, "show_own_leads_only_user_id");
        if ($show_own_leads_only_user_id) {
            $where .= " AND ($clients_table.owner_id=$show_own_leads_only_user_id)";
        }
        

        if($date_start and $date_end)
        {
            $where .= " AND ($clients_table.client_migration_date BETWEEN '$date_start' and '$date_end' OR $projects_table.created_date BETWEEN '$date_start' and '$date_end' OR $clients_table.created_date BETWEEN '$date_start' and '$date_end')";
        }

        
        $converted_to_client = "SELECT COUNT($clients_table.id) AS total
        FROM $clients_table
        LEFT JOIN $projects_table ON $projects_table.client_id = $clients_table.id
        WHERE $clients_table.deleted=0 AND $clients_table.status_id <> 2  AND $clients_table.is_lead=0 AND $clients_table.lead_status_id!=0 $where";

        $lead_statuses = "SELECT COUNT(DISTINCT $clients_table.id) AS total, $clients_table.lead_status_id, $lead_status_table.title, $lead_status_table.color, SUM($projects_table.price) AS projects_total
        FROM $clients_table
        LEFT JOIN $lead_status_table ON $lead_status_table.id = $clients_table.lead_status_id
        LEFT JOIN $projects_table ON $projects_table.client_id = $clients_table.id
        WHERE $clients_table.deleted=0 AND $clients_table.status_id <> 2 AND $clients_table.is_lead=1 $where
        GROUP BY $clients_table.lead_status_id
        ORDER BY total DESC";

        $client_statuses = "SELECT COUNT(DISTINCT $clients_table.id) AS total, 10 AS lead_status_id, 'Cliente' AS 'title', 'navy' AS color, SUM($projects_table.price) AS projects_total
        FROM $clients_table
        LEFT JOIN $projects_table ON $projects_table.client_id = $clients_table.id
        WHERE $clients_table.deleted=0  AND $clients_table.status_id <> 2  AND $clients_table.is_lead=0 $where";

        $total_sells = "SELECT SUM($projects_table.price) AS total, $clients_table.lead_status_id, $clients_table.currency_symbol
        FROM $projects_table
        INNER JOIN $clients_table ON $clients_table.id = $projects_table.client_id
        LEFT JOIN $lead_status_table ON $lead_status_table.id = $clients_table.lead_status_id
        WHERE $clients_table.deleted=0 $where
        ";

        $info = new \stdClass();
        $info->converted_to_client = $this->db->query($converted_to_client)->getRow()->total;
        $info->lead_statuses = $this->db->query($lead_statuses)->getResult();
        $info->client_statuses = $this->db->query($client_statuses)->getResult();
        $total_sells_result = $this->db->query($total_sells)->getRow();
        $info->total_sells = to_currency($total_sells_result->total, $total_sells_result->currency_symbol);

        return $info;
    }

    function get_lead_sources($options = array(), $date_start = null, $date_end = null) {
        $clients_table = $this->db->prefixTable('clients');
        $lead_status_table = $this->db->prefixTable('lead_status');
        $lead_source_table = $this->db->prefixTable('lead_source');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $estimate_value_items_table = $this->db->prefixTable('estimate_value_items');

        try {
            $this->db->query("SET sql_mode = ''");
        } catch (\Exception $e) {
            
        }
        $where = "";

        $show_own_leads_only_user_id = $this->_get_clean_value($options, "show_own_leads_only_user_id");
        if ($show_own_leads_only_user_id) {
            $where .= " AND ($clients_table.owner_id=$show_own_leads_only_user_id)";
        }

        if($date_start and $date_end)
        {
            $where .= " AND ($clients_table.created_date BETWEEN '$date_start' and '$date_end' OR $clients_table.client_migration_date BETWEEN '$date_start' and '$date_end')";
        }
        
        $converted_to_client = "SELECT COUNT(DISTINCT $clients_table.id) AS total
        FROM $clients_table
        LEFT JOIN $estimates_table ON $estimates_table.client_id = $clients_table.id
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 AND $clients_table.lead_status_id!=0 $where";

        $lead_sources = "SELECT
                SUM(total_leads) AS total_leads,
                SUM(total_clients) AS total_clients,
                lead_source_id,
                title,
                SUM(projects_total) AS projects_total
            FROM (
                SELECT 
                    0 AS total_leads,
                    COUNT(DISTINCT $clients_table.id) AS total_clients,
                    $clients_table.lead_source_id, 
                    COALESCE($lead_source_table.title, 'Outro') AS title, 
                    SUM(COALESCE(
                    CASE 
                        WHEN $estimate_value_items_table.is_checked = 1 THEN 
                            CASE 
                                WHEN $estimate_value_items_table.currency = 'BRL' THEN $estimate_value_items_table.converted_amount
                                ELSE $estimate_value_items_table.amount 
                            END
                        ELSE
                            CASE 
                                WHEN $estimate_value_items_table.currency = 'BRL' THEN $estimate_value_items_table.converted_amount
                                ELSE $estimate_value_items_table.amount 
                            END
                    END, 0)) AS projects_total
                FROM $clients_table
                LEFT JOIN $lead_source_table ON $lead_source_table.id = $clients_table.lead_source_id
                LEFT JOIN $estimates_table ON $estimates_table.client_id = $clients_table.id
                LEFT JOIN $estimate_value_items_table ON $estimate_value_items_table.estimate_id = $estimates_table.id AND $estimates_table.status = 'accepted'
                LEFT JOIN $estimate_items_table ON $estimate_items_table.estimate_id = $estimates_table.id AND $estimates_table.status = 'accepted'
                WHERE $clients_table.deleted = 0 AND $clients_table.status_id <> 2  AND $clients_table.is_lead = 0 $where
                GROUP BY $clients_table.lead_source_id
            UNION ALL
            
                SELECT 
                    COUNT(DISTINCT $clients_table.id) AS total_leads,
                    0 AS total_clients,
                    $clients_table.lead_source_id, 
                    COALESCE($lead_source_table.title, 'Outro') AS title, 
                    SUM(COALESCE(
                    CASE 
                        WHEN $estimate_value_items_table.is_checked = 1 THEN 
                            CASE 
                                WHEN $estimate_value_items_table.currency = 'BRL' THEN $estimate_value_items_table.converted_amount
                                ELSE $estimate_value_items_table.amount 
                            END
                        ELSE
                            CASE 
                                WHEN $estimate_value_items_table.currency = 'BRL' THEN $estimate_value_items_table.converted_amount
                                ELSE $estimate_value_items_table.amount 
                            END
                    END, 0)) AS projects_total
                FROM $clients_table
                LEFT JOIN $lead_source_table ON $lead_source_table.id = $clients_table.lead_source_id
                LEFT JOIN $estimates_table ON $estimates_table.client_id = $clients_table.id
                LEFT JOIN $estimate_value_items_table ON $estimate_value_items_table.estimate_id = $estimates_table.id AND $estimates_table.status = 'accepted'
                LEFT JOIN $estimate_items_table ON $estimate_items_table.estimate_id = $estimates_table.id AND $estimates_table.status = 'accepted'
                WHERE $clients_table.deleted = 0 AND $clients_table.status_id <> 2  AND $clients_table.is_lead = 1 $where
                GROUP BY $clients_table.lead_source_id
            ) AS combined
            GROUP BY title
        ";

        $total_sells = "SELECT SUM($projects_table.price) AS total, $clients_table.lead_status_id, $clients_table.currency_symbol
        FROM $projects_table
        INNER JOIN $clients_table ON $clients_table.id = $projects_table.client_id
        LEFT JOIN $lead_status_table ON $lead_status_table.id = $clients_table.lead_status_id
        WHERE $clients_table.deleted=0 AND $projects_table.deleted=0 $where
        ";

        $info = new \stdClass();
        $info->converted_to_client = $this->db->query($converted_to_client)->getRow()->total;
        $info->lead_sources = $this->db->query($lead_sources)->getResult();
        $total_sells_result = $this->db->query($total_sells)->getRow();
        $info->total_sells = to_currency($total_sells_result->total, $total_sells_result->currency_symbol);

        return $info;
    }
    
    function get_statuses() {
        $clients_table = $this->db->prefixTable('clients');
        $client_status_table = $this->db->prefixTable('client_status');
        $client_statuses = "SELECT COUNT($clients_table.id) AS total, $clients_table.status_id, $client_status_table.title, $client_status_table.color
        FROM $clients_table
        LEFT JOIN $client_status_table ON $client_status_table.id = $clients_table.status_id
        GROUP BY $clients_table.status_id
        ORDER BY $client_status_table.sort ASC";
        return $this->db->query($client_statuses)->getResult();
    }
}
