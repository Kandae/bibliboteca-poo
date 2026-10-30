<?php
namespace App\Multas;

use App\Interfaces\MultaInterface;

class MultaPadrao implements MultaInterface {
    public function calcularMulta(int $diasAtraso): float {
        return $diasAtraso > 0 ? $diasAtraso * 2.00 : 0.0;
    }
}