<?php
namespace App\Multas;

use App\Interfaces\MultaInterface;

class MultaProfessor implements MultaInterface {
    public function calcularMulta(int $diasAtraso): float {
        return $diasAtraso > 0 ? $diasAtraso * 1.00 : 0.0;
    }
}