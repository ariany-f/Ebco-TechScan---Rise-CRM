<?php

namespace App\Models;

class Custom_fields_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'custom_fields';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $custom_fields_table = $this->db->prefixTable('custom_fields');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $custom_fields_table.id=$id";
        }


        $related_to = $this->_get_clean_value($options, "related_to");
        if ($related_to) {
            $where .= " AND $custom_fields_table.related_to='$related_to'";
        }

        $add_filter = $this->_get_clean_value($options, "add_filter");
        if ($add_filter) {
            $where .= " AND $custom_fields_table.add_filter=1";
        }

        $show_in_table = $this->_get_clean_value($options, "show_in_table");
        if ($show_in_table) {
            $where .= " AND $custom_fields_table.show_in_table=1";
        }

        $show_in_invoice = $this->_get_clean_value($options, "show_in_invoice");
        if ($show_in_invoice) {
            $where .= " AND $custom_fields_table.show_in_invoice=1";
        }

        $show_in_estimate = $this->_get_clean_value($options, "show_in_estimate");
        if ($show_in_estimate) {
            $where .= " AND $custom_fields_table.show_in_estimate=1";
        }

        $show_in_contract = $this->_get_clean_value($options, "show_in_contract");
        if ($show_in_contract) {
            $where .= " AND $custom_fields_table.show_in_contract=1";
        }

        $show_in_proposal = $this->_get_clean_value($options, "show_in_proposal");
        if ($show_in_proposal) {
            $where .= " AND $custom_fields_table.show_in_proposal=1";
        }

        $show_in_order = $this->_get_clean_value($options, "show_in_order");
        if ($show_in_order) {
            $where .= " AND $custom_fields_table.show_in_order=1";
        }

        $show_in_embedded_form = $this->_get_clean_value($options, "show_in_embedded_form");
        if ($show_in_embedded_form) {
            $where .= " AND $custom_fields_table.show_in_embedded_form=1";
        }

        $sql = "SELECT $custom_fields_table.*
        FROM $custom_fields_table
        WHERE $custom_fields_table.deleted=0 $where
        ORDER by $custom_fields_table.sort ASC";

        /**Get options from another table */
        $sql_options = "SELECT $custom_fields_table.*
                        FROM $custom_fields_table
                        WHERE $custom_fields_table.deleted=0
                        AND (LENGTH($custom_fields_table.options) - LENGTH(REPLACE($custom_fields_table.options, ',', '')) + 1) = 1
                        AND LOCATE('crm_', $custom_fields_table.options) >= 1
                        $where 
                        ORDER by $custom_fields_table.sort ASC";

        $foreign_table_options_custom_field = $this->db->query($sql_options)->getRow();

        if(!empty($foreign_table_options_custom_field))
        {
            $sql = "SELECT
            crm_custom_fields.id,
            crm_custom_fields.title,
            crm_custom_fields.placeholder_language_key,
            crm_custom_fields.title_language_key,
            crm_custom_fields.show_in_embedded_form,
            crm_custom_fields.placeholder,
            crm_custom_fields.example_variable_name,
            IF(((LENGTH(crm_custom_fields.options) - LENGTH(REPLACE(crm_custom_fields.options, ',', '')) + 1) = 1
                                    AND LOCATE('crm_', crm_custom_fields.options) >= 1), (SELECT GROUP_CONCAT(CONCAT(mock.id, ':', mock.first_name, ' ', mock.last_name) SEPARATOR ',')
                 FROM $foreign_table_options_custom_field->options AS mock 
                 WHERE mock.deleted = 0
                      AND (
                           crm_custom_fields.options != 'crm_users' -- Verificação condicional
                           OR (crm_custom_fields.options = 'crm_users' AND mock.user_type = 'staff')
                       )), 
                crm_custom_fields.options
            ) AS options,
            crm_custom_fields.field_type,
            crm_custom_fields.related_to,
            crm_custom_fields.sort,
            crm_custom_fields.required,
            crm_custom_fields.add_filter,
            crm_custom_fields.show_in_table,
            crm_custom_fields.show_in_invoice,
            crm_custom_fields.show_in_estimate,
            crm_custom_fields.show_in_contract,
            crm_custom_fields.show_in_order,
            crm_custom_fields.show_in_proposal,
            crm_custom_fields.visible_to_admins_only,
            crm_custom_fields.hide_from_clients,
            crm_custom_fields.disable_editing_by_clients,
            crm_custom_fields.show_on_kanban_card,
            crm_custom_fields.deleted,
            crm_custom_fields.show_in_subscription
                FROM $custom_fields_table
                WHERE $custom_fields_table.deleted=0 $where
                ORDER by $custom_fields_table.sort ASC";
        }
        
        return $this->db->query($sql);
    }

    function get_max_sort_value($related_to = "") {
        $custom_fields_table = $this->db->prefixTable('custom_fields');

        $sql = "SELECT MAX($custom_fields_table.sort) as sort
        FROM $custom_fields_table
        WHERE $custom_fields_table.deleted=0 AND $custom_fields_table.related_to='$related_to'";
        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            return $result->getRow()->sort;
        } else {
            return 0;
        }
    }

    function get_combined_details($related_to, $related_to_id = 0, $is_admin = 0, $user_type = "") {
        $custom_fields_table = $this->db->prefixTable('custom_fields');
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');

        $where = "";

        //check visibility permission for non-admin users
        if (!$is_admin) {
            $where .= " AND $custom_fields_table.visible_to_admins_only=0";
        }


        //check visibility permission for clients
        if ($user_type === "client") {
            $where .= " AND $custom_fields_table.hide_from_clients=0";
        }


        if (!$related_to_id) {
            $related_to_id = 0;
        }

        $related_to_id = $related_to_id ? $this->db->escapeString($related_to_id) : $related_to_id;

        $sql = "SELECT $custom_fields_table.*,
                $custom_field_values_table.id AS custom_field_values_id, $custom_field_values_table.value
        FROM $custom_fields_table
        LEFT JOIN $custom_field_values_table ON $custom_fields_table.id= $custom_field_values_table.custom_field_id AND $custom_field_values_table.deleted=0 AND $custom_field_values_table.related_to_id = $related_to_id
        WHERE $custom_fields_table.deleted=0 AND $custom_fields_table.related_to = '$related_to' $where
        ORDER by $custom_fields_table.sort ASC";

            /**Get options from another table */
            $sql_options = "SELECT $custom_fields_table.*,
                $custom_field_values_table.id AS custom_field_values_id, $custom_field_values_table.value
            FROM $custom_fields_table
            LEFT JOIN $custom_field_values_table ON $custom_fields_table.id= $custom_field_values_table.custom_field_id AND $custom_field_values_table.deleted=0 AND $custom_field_values_table.related_to_id = $related_to_id
            WHERE $custom_fields_table.deleted=0 AND $custom_fields_table.related_to = '$related_to' 
            AND (LENGTH($custom_fields_table.options) - LENGTH(REPLACE($custom_fields_table.options, ',', '')) + 1) = 1
            AND LOCATE('crm_', $custom_fields_table.options) >= 1
            $where
            ORDER by $custom_fields_table.sort ASC";

            $foreign_table_options_custom_field = $this->db->query($sql_options)->getRow();

            if(!empty($foreign_table_options_custom_field))
            {
                $sql = "SELECT
                    crm_custom_fields.id,
                    crm_custom_fields.title,
                    crm_custom_fields.placeholder_language_key,
                    crm_custom_fields.title_language_key,
                    crm_custom_fields.show_in_embedded_form,
                    crm_custom_fields.placeholder,
                    crm_custom_fields.example_variable_name,
                    IF(((LENGTH(crm_custom_fields.options) - LENGTH(REPLACE(crm_custom_fields.options, ',', '')) + 1) = 1
                                AND LOCATE('crm_', crm_custom_fields.options) >= 1),(SELECT GROUP_CONCAT(CONCAT(mock.id, ':', mock.first_name, ' ', mock.last_name) SEPARATOR ',')
                 FROM $foreign_table_options_custom_field->options AS mock WHERE mock.deleted = 0 AND mock.status = 'active' 
                      AND (
                           crm_custom_fields.options != 'crm_users' -- Verificação condicional
                           OR (crm_custom_fields.options = 'crm_users' AND mock.user_type = 'staff')
                       )), 
                crm_custom_fields.options
            ) AS options,
                    crm_custom_fields.field_type,
                    crm_custom_fields.related_to,
                    crm_custom_fields.sort,
                    crm_custom_fields.required,
                    crm_custom_fields.add_filter,
                    crm_custom_fields.show_in_table,
                    crm_custom_fields.show_in_invoice,
                    crm_custom_fields.show_in_estimate,
                    crm_custom_fields.show_in_contract,
                    crm_custom_fields.show_in_order,
                    crm_custom_fields.show_in_proposal,
                    crm_custom_fields.visible_to_admins_only,
                    crm_custom_fields.hide_from_clients,
                    crm_custom_fields.disable_editing_by_clients,
                    crm_custom_fields.show_on_kanban_card,
                    crm_custom_fields.deleted,
                    crm_custom_fields.show_in_subscription,
                    $custom_field_values_table.id AS custom_field_values_id, $custom_field_values_table.value
                    FROM $custom_fields_table
                    LEFT JOIN $custom_field_values_table ON $custom_fields_table.id= $custom_field_values_table.custom_field_id AND $custom_field_values_table.deleted=0 AND $custom_field_values_table.related_to_id = $related_to_id
                    WHERE $custom_fields_table.deleted=0 AND $custom_fields_table.related_to = '$related_to' $where
                    ORDER by $custom_fields_table.sort ASC";
            }

        return $this->db->query($sql);
    }

    function get_custom_field_headers_for_table($related_to, $is_admin = 0, $user_type = "") {
        $custom_fields_for_table = $this->get_available_fields_for_table($related_to, $is_admin, $user_type);

        $json_string = "";
        foreach ($custom_fields_for_table as $column) {
            $json_string .= ',' . '{"title":"' . $column->title . '"}';
        }

        return $json_string;
    }

    function get_available_fields_for_table($related_to, $is_admin = 0, $user_type = "") {
        $custom_fields_table = $this->db->prefixTable('custom_fields');

        $where = "";

        //check visibility permission for non-admin users
        if (!$is_admin) {
            $where .= " AND $custom_fields_table.visible_to_admins_only=0";
        }


        //check visibility permission for clients
        if ($user_type === "client") {
            $where .= " AND $custom_fields_table.hide_from_clients=0";
        }


        $sql = "SELECT id, title, field_type
                FROM $custom_fields_table
                WHERE $custom_fields_table.related_to='$related_to' AND $custom_fields_table.show_in_table=1 AND $custom_fields_table.deleted=0 $where    
                ORDER BY $custom_fields_table.sort ASC";

        return $this->db->query($sql)->getResult();
    }

    function get_custom_field_filters($related_to, $is_admin = 0, $user_type = "") {
        $custom_fields_for_filter = $this->get_available_filters($related_to, $is_admin, $user_type);

        $json_string = "";
        foreach ($custom_fields_for_filter as $column) {
            if ($json_string) {
                $json_string .= ",";
            }

            $json_string .= '{name: "custom_field_filter_' . $column->id . '", class: "w200", options: ' . $this->prepare_custom_field_filter_dropdown($column->title, $column->options) . '}';
        }

        return $json_string;
    }

    private function prepare_custom_field_filter_dropdown($title = "", $options = "") {
        $groups_dropdown = array(array("id" => "", "text" => "- " . $title . " -"));

        $options = explode(',', $options);
        foreach ($options as $option) {
            if (strpos($option, ':') !== false) {
                $op = explode(":", $option);
                $id = $op[0];
                $name = $op[1];
            }
            else
            {
                $id = $option;
                $name = $option;
            }
            $groups_dropdown[] = array("id" => $id, "text" => $name);
        }

        return json_encode($groups_dropdown);
    }

    function get_available_filters($related_to, $is_admin = 0, $user_type = "") {
        $custom_fields_table = $this->db->prefixTable('custom_fields');

        $where = "";

        //check visibility permission for non-admin users
        if (!$is_admin) {
            $where .= " AND $custom_fields_table.visible_to_admins_only=0";
        }


        //check visibility permission for clients
        if ($user_type === "client") {
            $where .= " AND $custom_fields_table.hide_from_clients=0";
        }


        $sql = "SELECT id, title, options
                FROM $custom_fields_table
                WHERE $custom_fields_table.related_to='$related_to' AND $custom_fields_table.add_filter=1 AND $custom_fields_table.deleted=0 AND ($custom_fields_table.field_type='select' OR $custom_fields_table.field_type='multi_select') $where    
                ORDER BY $custom_fields_table.sort ASC";

        
        /**Get options from another table */
        $sql_options = "SELECT id, title, options
            FROM $custom_fields_table
            WHERE $custom_fields_table.related_to='$related_to' AND $custom_fields_table.add_filter=1 AND $custom_fields_table.deleted=0 AND ($custom_fields_table.field_type='select' OR $custom_fields_table.field_type='multi_select') $where    
            AND (LENGTH($custom_fields_table.options) - LENGTH(REPLACE($custom_fields_table.options, ',', '')) + 1) = 1
            AND LOCATE('crm_', $custom_fields_table.options) >= 1
            ORDER BY $custom_fields_table.sort ASC";

        $foreign_table_options_custom_field = $this->db->query($sql_options)->getRow();

        if(!empty($foreign_table_options_custom_field))
        {
            $sql = "SELECT
                crm_custom_fields.id,
                crm_custom_fields.title,
                IF(((LENGTH(crm_custom_fields.options) - LENGTH(REPLACE(crm_custom_fields.options, ',', '')) + 1) = 1
                            AND LOCATE('crm_', crm_custom_fields.options) >= 1),(SELECT GROUP_CONCAT(CONCAT(mock.id, ':', mock.first_name, ' ', mock.last_name) SEPARATOR ',')
                 FROM $foreign_table_options_custom_field->options AS mock
                    WHERE mock.deleted = 0
                      AND (
                           crm_custom_fields.options != 'crm_users' -- Verificação condicional
                           OR (crm_custom_fields.options = 'crm_users' AND mock.user_type = 'staff')
                       )),
                crm_custom_fields.options
            ) AS options
                FROM $custom_fields_table
                WHERE $custom_fields_table.related_to='$related_to' AND $custom_fields_table.add_filter=1 AND $custom_fields_table.deleted=0 AND ($custom_fields_table.field_type='select' OR $custom_fields_table.field_type='multi_select') $where    
                ORDER by $custom_fields_table.sort ASC";
        }

        return $this->db->query($sql)->getResult();
    }

    function get_email_template_variables_array($related_to, $related_to_id = 0, $is_admin = 0, $user_type = "") {
        $tickets_template_variables = $this->get_combined_details($related_to, $related_to_id, $is_admin, $user_type)->getResult();
        $variables_array = array();

        foreach ($tickets_template_variables as $variable) {
            if ($variable->example_variable_name) {
                array_push($variables_array, $variable->example_variable_name);
            }
        }

        return $variables_array;
    }

}
