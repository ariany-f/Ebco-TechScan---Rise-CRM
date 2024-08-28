<?php

namespace App\Models;

class Company_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'company';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $company_table = $this->db->prefixTable('company');
        
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $company_table.id=$id";
        }
        
        $is_default = $this->_get_clean_value($options, "is_default");
        if ($is_default) {
            $where = " AND $company_table.is_default=1";
        }

        $sql = "SELECT $company_table.*
        FROM $company_table
        WHERE $company_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_company_from_name($company_name = "") {
        $company_table = $this->db->prefixTable('company');

        $sql = "SELECT $company_table.id 
        FROM $company_table
        WHERE $company_table.deleted=0 AND TRIM($company_table.name)='$company_name'
        LIMIT 1";

        return $this->db->query($sql)->getRow();
    }

    function remove_other_default_company($except_id) {
        $company_table = $this->db->prefixTable('company');

        $sql = "UPDATE $company_table SET $company_table.is_default=0 WHERE $company_table.id!=$except_id AND $company_table.is_default=1; ";
        $this->db->query($sql);
    }

}
