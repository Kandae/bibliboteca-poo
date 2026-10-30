<?php
namespace App\Models;

class EBook extends Material {
    private string $formatoArquivo;
    private float $tamanhoArquivo;
    private string $linkAcesso;
    private int $maxAcessosSimultaneos;
    private int $acessosAtivos = 0;

    public function __construct(string $codigo, string $titulo, string $autor, int $anoPublicacao, string $formatoArquivo, float $tamanhoArquivo, string $linkAcesso, int $maxAcessosSimultaneos) {
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, 1);
        $this->formatoArquivo = $formatoArquivo;
        $this->tamanhoArquivo = $tamanhoArquivo;
        $this->linkAcesso = $linkAcesso;
        $this->maxAcessosSimultaneos = $maxAcessosSimultaneos;
    }

    public function getPrazoMaximoEmprestimo(): int {
        return 21;
    }

    public function consultarDisponibilidade(): bool {
        return $this->acessosAtivos < $this->maxAcessosSimultaneos;
    }

    public function emprestarExemplar(): bool {
        if ($this->consultarDisponibilidade()) {
            $this->acessosAtivos++;
            return true;
        }
        return false;
    }

    public function devolverExemplar(): void {
        if ($this->acessosAtivos > 0) {
            $this->acessosAtivos--;
        }
    }

    public function getAcessosAtivos(): int { return $this->acessosAtivos; }
    public function getMaxAcessosSimultaneos(): int { return $this->maxAcessosSimultaneos; }
}