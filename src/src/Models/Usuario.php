<?php
namespace App\Models;

abstract class Usuario {
    protected string $matricula;
    protected string $nome;
    protected string $email;
    protected string $telefone;
    protected string $status; // 'ATIVO', 'BLOQUEADO'
    protected int $quantidadeMaxEmprestimos;
    protected int $emprestimosAtivos = 0;

    public function __construct(string $matricula, string $nome, string $email, string $telefone, int $quantidadeMaxEmprestimos) {
        $this->matricula = $matricula;
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->status = 'ATIVO';
        $this->quantidadeMaxEmprestimos = $quantidadeMaxEmprestimos;
    }

    public function podeRealizarEmprestimo(): bool {
        return $this->status === 'ATIVO' && $this->emprestimosAtivos < $this->quantidadeMaxEmprestimos;
    }

    public function incrementarEmprestimo(): void {
        $this->emprestimosAtivos++;
    }

    public function decrementarEmprestimo(): void {
        if ($this->emprestimosAtivos > 0) {
            $this->emprestimosAtivos--;
        }
    }

    abstract public function getDiasAdicionais(): int;

    public function getMatricula(): string { return $this->matricula; }
    public function getNome(): string { return $this->nome; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getEmprestimosAtivos(): int { return $this->emprestimosAtivos; }
    public function getLimiteEmprestimos(): int { return $this->quantidadeMaxEmprestimos; }
}