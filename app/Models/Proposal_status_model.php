<?php

namespace App\Models;

class Proposal_status_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'proposal_status';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $proposal_status_table = $this->db->prefixTable('proposal_status');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $proposal_status_table.id=$id";
        }

        $sql = "SELECT $proposal_status_table.*, 0 AS total_proposals
        FROM $proposal_status_table
        WHERE $proposal_status_table.deleted=0 $where
        ORDER BY $proposal_status_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_max_sort_value() {
        $proposal_status_table = $this->db->prefixTable('proposal_status');

        $sql = "SELECT MAX($proposal_status_table.sort) as sort
        FROM $proposal_status_table
        WHERE $proposal_status_table.deleted=0";
        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            return $result->getRow()->sort;
        } else {
            return 0;
        }
    }

    function get_first_status() {
        $proposal_status_table = $this->db->prefixTable('proposal_status');

        $sql = "SELECT $proposal_status_table.id AS first_proposal_status
        FROM $proposal_status_table
        WHERE $proposal_status_table.deleted=0
        ORDER BY $proposal_status_table.sort ASC
        LIMIT 1";

        return $this->db->query($sql)->getRow()->first_proposal_status;
    }

}
