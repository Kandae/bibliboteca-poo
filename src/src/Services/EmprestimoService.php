<?php
namespace App\Services;

require_once __DIR__ . '/../Interfaces/MultaInterface.php';
require_once __DIR__ . '/../Multas/MultaPadrao.php';
require_once __DIR__ . '/../Multas/MultaProfessor.php';

use App\Models\Usuario;
use App\Models\Material;
use App\Models\Emprestimo;
use App\Models\Historico;
use App\Models\Professor;
use App\Interfaces\MultaInterface;
use App\Multas\MultaPadrao;
use App\Multas\MultaProfessor;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Exceptions\EmprestimoInvalidoException;
use DateTime;

class EmprestimoService {
    private array $historico = [];

    public function criarEmprestimo(string $codigo, Usuario $usuario, Material $material, ?DateTime $dataInicio = null): Emprestimo {
        if ($usuario->getStatus() === 'BLOQUEADO') {
            throw new UsuarioBloqueadoException("O usuário {$usuario->getNome()} está bloqueado.");
        }

        if ($usuario->getEmprestimosAtivos() >= $usuario->getLimiteEmprestimos()) {
            throw new LimiteEmprestimoException("O usuário {$usuario->getNome()} atingiu o limite de empréstimos.");
        }

        if (!$material->consultarDisponibilidade()) {
            throw new MaterialIndisponivelException("O material '{$material->getTitulo()}' não está disponível.");
        }

        $dataEmprestimo = $dataInicio ?? new DateTime();
        $diasPrazo = $material->getPrazoMaximoEmprestimo() + $usuario->getDiasAdicionais();
        
        $dataPrevista = clone $dataEmprestimo;
        $dataPrevista->modify("+{$diasPrazo} days");

        $material->emprestarExemplar();
        $usuario->incrementarEmprestimo();

        $emprestimo = new Emprestimo($codigo, $usuario, $material, $dataEmprestimo, $dataPrevista);
        
        $hist = new Historico("EMPRÉSTIMO REALIZADO", $usuario, $material);
        $this->historico[] = $hist->registrar();

        return $emprestimo;
    }

    public function registrarDevolucao(Emprestimo $emprestimo, ?DateTime $dataDevolucao = null): array {
        if ($emprestimo->getStatus() === 'DEVOLVIDO') {
            throw new EmprestimoInvalidoException("O empréstimo já foi devolvido.");
        }
        if ($emprestimo->getStatus() === 'CANCELADO') {
            throw new EmprestimoInvalidoException("Não é possível devolver um empréstimo cancelado.");
        }

        $dataReal = $dataDevolucao ?? new DateTime();
        $emprestimo->setDataRealDevolucao($dataReal);

        $diasAtraso = 0;
        $multaValor = 0.0;

        if ($dataReal > $emprestimo->getDataPrevista()) {
            $intervalo = $emprestimo->getDataPrevista()->diff($dataReal);
            $diasAtraso = (int)$intervalo->format('%a');
            
            $calculadorMulta = $this->obterEstrategiaMulta($emprestimo->getUsuario());
            $multaValor = $calculadorMulta->calcularMulta($diasAtraso);
            
            $emprestimo->setStatus('ATRASADO');
        } else {
            $emprestimo->setStatus('DEVOLVIDO');
        }

        $emprestimo->getMaterial()->devolverExemplar();
        $emprestimo->getUsuario()->decrementarEmprestimo();

        $obs = $diasAtraso > 0 ? "Com $diasAtraso dias de atraso. Multa: R$ " . number_format($multaValor, 2, ',', '.') : "Devoluçao no prazo";
        $hist = new Historico("DEVOLUÇÃO REALIZADA", $emprestimo->getUsuario(), $emprestimo->getMaterial(), $obs);
        $this->historico[] = $hist->registrar();

        return [
            'diasAtraso' => $diasAtraso,
            'multa' => $multaValor
        ];
    }

    public function obterEstrategiaMulta(Usuario $usuario): MultaInterface {
        if ($usuario instanceof Professor) {
            return new MultaProfessor();
        }
        return new MultaPadrao();
    }

    public function getHistorico(): array {
        return $this->historico;
    }
}