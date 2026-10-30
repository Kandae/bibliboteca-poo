<?php
namespace App\Models;

abstract class Material {
    protected string $codigo;
    protected string $titulo;
    protected string $autor;
    protected int $anoPublicacao;
    protected bool $disponibilidade;
    protected int $quantidadeExemplares;

    public function __construct(string $codigo, string $titulo, string $autor, int $anoPublicacao, int $quantidadeExemplares) {
        $this->codigo = $codigo;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->anoPublicacao = $anoPublicacao;
        $this->quantidadeExemplares = max(0, $quantidadeExemplares);
        $this->disponibilidade = $this->quantidadeExemplares > 0;
    }

    abstract public function getPrazoMaximoEmprestimo(): int;

    public function consultarDisponibilidade(): bool {
        return $this->disponibilidade;
    }

    public function consultarQuantidadeDisponivel(): int {
        return $this->quantidadeExemplares;
    }

    public function emprestarExemplar(): bool {
        if ($this->quantidadeExemplares > 0) {
            $this->quantidadeExemplares--;
            if ($this->quantidadeExemplares === 0) {
                $this->disponibilidade = false;
            }
            return true;
        }
        return false;
    }

    public function devolverExemplar(): void {
        $this->quantidadeExemplares++;
        $this->disponibilidade = true;
    }

    public function exibirInformacoes(): string {
        return "[$this->codigo] $this->titulo - Autor: $this->autor ($this->anoPublicacao) | Estoque: $this->quantidadeExemplares";
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getTitulo(): string { return $this->titulo; }
}