<?php
namespace App\Models;

class Revista extends Material {
    private int $edicao;
    private int $volume;
    private string $mesPublicacao;

    public function __construct(string $codigo, string $titulo, string $autor, int $anoPublicacao, int $quantidade, int $edicao, int $volume, string $mesPublicacao) {
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, $quantidade);
        $this->edicao = $edicao;
        $this->volume = $volume;
        $this->mesPublicacao = $mesPublicacao;
    }

    public function getPrazoMaximoEmprestimo(): int {
        return 7;
    }
}