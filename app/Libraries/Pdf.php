<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/tcpdf/tcpdf.php";

class Pdf extends \TCPDF {

    public function __construct() {
        parent::__construct();
    }

      // Configurar cabeçalho
      public function Header() {
        $image_file = K_PATH_IMAGES . 'header.png'; // Certifique-se de ajustar o caminho da imagem
        $this->Image($image_file, 10, 10, 190, 40, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetY(50); // Define a posição após o cabeçalho
    }

    // Configurar rodapé
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Data de Emissão ' . date('Y-m-d'), 0, 0, 'L');
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

}
