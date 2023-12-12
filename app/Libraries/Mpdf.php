<?php

namespace App\Libraries;

require_once APPPATH . '/ThirdParty/vendor/autoload.php';

class Mpdf extends \Mpdf\Mpdf {

    public function __construct() {
        parent::__construct();
    }

}
