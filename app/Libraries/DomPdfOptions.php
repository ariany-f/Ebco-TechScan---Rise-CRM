<?php

namespace App\Libraries;

require_once APPPATH . '/ThirdParty/vendor/autoload.php';

class DomPdfOptions extends \Dompdf\Options {

    public function __construct() {
        parent::__construct();
    }

}
