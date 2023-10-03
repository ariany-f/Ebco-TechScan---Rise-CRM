<?php

namespace App\Models;

class Estimate_type_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimate_type';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimate_type_table = $this->db->prefixTable('estimate_type');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $estimate_type_table.id=$id";
        }

        $sql = "SELECT $estimate_type_table.*
        FROM $estimate_type_table
        WHERE $estimate_type_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
