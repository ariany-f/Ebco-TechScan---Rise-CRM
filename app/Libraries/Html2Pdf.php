<?php

namespace App\Libraries;

require_once APPPATH . '/ThirdParty/vendor/autoload.php';

class Html2Pdf extends \Spipu\Html2Pdf\Html2Pdf {

    public function __construct() {
        parent::__construct();
    }

}
