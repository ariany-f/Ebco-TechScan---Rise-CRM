<?php

namespace App\Libraries;

require_once APPPATH . '/ThirdParty/vendor/autoload.php';

class DomPdf extends \Dompdf\Dompdf {

    public function __construct() {
        parent::__construct();
    }

}
