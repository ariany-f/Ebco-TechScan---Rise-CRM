<?php

namespace App\Models;

class Estimate_templates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimate_templates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimate_templates_table = $this->db->prefixTable('estimate_templates');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $estimate_templates_table.id=$id";
        }

        $sql = "SELECT $estimate_templates_table.*
        FROM $estimate_templates_table
        WHERE $estimate_templates_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
