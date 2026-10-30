<?php
namespace App\Models;

class Professor extends Usuario {
    public function __construct(string $matricula, string $nome, string $email, string $telefone = '') {
        parent::__construct($matricula, $nome, $email, $telefone, 5);
    }

    public function getDiasAdicionais(): int {
        return 7;
    }
}