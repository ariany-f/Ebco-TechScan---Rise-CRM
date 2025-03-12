<?php

namespace App\Models;

class Estimate_value_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimate_value_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimate_value_items_table = $this->db->prefixTable("estimate_value_items");
        $estimates_table = $this->db->prefixTable("estimates");

        $where = "";

        $estimate_id = $this->_get_clean_value($options, "estimate_id");
        if ($estimate_id) {
            $where .= " AND $estimate_value_items_table.estimate_id=$estimate_id";
        }

        $checked = $this->_get_clean_value($options, "checked");
        if ($checked) {
            $where .= " AND $estimate_value_items_table.is_checked='$checked'";
        }

        $sql = "SELECT $estimate_value_items_table.*, IF($estimate_value_items_table.sort!=0, $estimate_value_items_table.sort, $estimate_value_items_table.id) AS new_sort
        FROM $estimate_value_items_table
        INNER JOIN $estimates_table ON $estimates_table.deleted = 0 AND (($estimates_table.id = $estimate_value_items_table.estimate_id))
        WHERE $estimate_value_items_table.deleted=0 AND $estimate_value_items_table.estimate_id IS NOT NULL $where
        ORDER BY new_sort ASC";
        return $this->db->query($sql);
    }

    function get_all_checklist_of_project($project_id) {
        $estimate_value_items_table = $this->db->prefixTable('estimate_value_items');
        $estimates_table = $this->db->prefixTable('estimates');

        $sql = "SELECT $estimate_value_items_table.estimate_id, $estimate_value_items_table.title
        FROM $estimate_value_items_table
        INNER JOIN $estimates_table ON $estimates_table.deleted = 0 AND $estimates_table.id = $estimate_value_items_table.estimate_id
        WHERE $estimate_value_items_table.deleted=0 AND $estimate_value_items_table.estimate_id IS NOT NULL AND $estimate_value_items_table.estimate_id IN(SELECT $estimates_table.id FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.project_id=$project_id)";
        return $this->db->query($sql);
    }

}
