<?php
namespace App\Models;

use DateTime;

class Historico {
    private DateTime $dataHora;
    private string $acao;
    private Usuario $usuario;
    private Material $material;
    private string $observacao;

    public function __construct(string $acao, Usuario $usuario, Material $material, string $observacao = '') {
        $this->dataHora = new DateTime();
        $this->acao = $acao;
        $this->usuario = $usuario;
        $this->material = $material;
        $this->observacao = $observacao;
    }

    public function registrar(): string {
        return sprintf(
            "[%s] AÇÃO: %s | Usuário: %s | Material: %s %s",
            $this->dataHora->format('d/m/Y H:i:s'),
            $this->acao,
            $this->usuario->getNome(),
            $this->material->getTitulo(),
            $this->observacao ? "| Obs: " . $this->observacao : ""
        );
    }
}