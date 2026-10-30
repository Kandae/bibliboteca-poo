<?php
namespace App\Models;

use DateTime;

class Emprestimo {
    private string $codigo;
    private Usuario $usuario;
    private Material $material;
    private DateTime $dataEmprestimo;
    private DateTime $dataPrevista;
    private ?DateTime $dataRealDevolucao = null;
    private string $status; // ATIVO, DEVOLVIDO, ATRASADO, CANCELADO

    public function __construct(string $codigo, Usuario $usuario, Material $material, DateTime $dataEmprestimo, DateTime $dataPrevista) {
        $this->codigo = $codigo;
        $this->usuario = $usuario;
        $this->material = $material;
        $this->dataEmprestimo = $dataEmprestimo;
        $this->dataPrevista = $dataPrevista;
        $this->status = 'ATIVO';
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function getMaterial(): Material { return $this->material; }
    public function getDataEmprestimo(): DateTime { return $this->dataEmprestimo; }
    public function getDataPrevista(): DateTime { return $this->dataPrevista; }
    public function getDataRealDevolucao(): ?DateTime { return $this->dataRealDevolucao; }
    public function setDataRealDevolucao(DateTime $data): void { $this->dataRealDevolucao = $data; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
}