<?php
namespace App\Interfaces;

interface MultaInterface {
    public function calcularMulta(int $diasAtraso): float;
}