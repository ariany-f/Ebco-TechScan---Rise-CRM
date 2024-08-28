<?php

namespace App\Models;

class LogEmails_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'log_mail';
        parent::__construct($this->table);
    }

    function create_log($event) {
      
        $data = array(
            "email" => $event['email'],
            "bcc" => $event['bcc'],
            "cc" => $event['cc'],
            "subject" => $event['subject'],
            "message" => $event['message'],
            "result" => $event['result']
        );

        $log_id = $this->ci_save($data);
    }
}
