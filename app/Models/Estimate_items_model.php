<?php

namespace App\Models;

class Estimate_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimate_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimate_items_table = $this->db->prefixTable('estimate_items');
        $estimates_table = $this->db->prefixTable('estimates');
        $clients_table = $this->db->prefixTable('clients');
        $items_table = $this->db->prefixTable('items');
        $categories_table = $this->db->prefixTable('item_categories');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $estimate_items_table.id=$id";
        }
        $estimate_id = $this->_get_clean_value($options, "estimate_id");
        if ($estimate_id) {
            $where .= " AND $estimate_items_table.estimate_id=$estimate_id";
        }

        $sql = "SELECT $estimate_items_table.*, $categories_table.title AS category, $items_table.title, $items_table.files, $items_table.category_id, $items_table.rate, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$estimates_table.client_id limit 1) AS currency_symbol, $estimates_table.created_by
        FROM $estimate_items_table
        LEFT JOIN $estimates_table ON $estimates_table.id=$estimate_items_table.estimate_id
        LEFT JOIN $items_table ON $items_table.id=$estimate_items_table.item_id
        LEFT JOIN $categories_table ON $categories_table.id=$items_table.category_id
        WHERE $estimate_items_table.deleted=0 $where
        ORDER BY $estimate_items_table.sort ASC";
        return $this->db->query($sql);
    }

}
