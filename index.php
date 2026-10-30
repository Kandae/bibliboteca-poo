<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Livro;
use App\Models\Revista;
use App\Models\EBook;
use App\Services\EmprestimoService;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Exceptions\EmprestimoInvalidoException;

$service = new EmprestimoService();
$emprestimoService = $service;


$alunoAna = new Aluno("A001", "Ana Souza", "ana@email.com");
$profCarlos = new Professor("P001", "Carlos Mendes", "carlos@email.com");

$livroPOO = new Livro("L001", "Programação Orientada a Objetos com PHP", "João Silva", 2021, 3, "978-3-16-148410-0", 350, "Editora Tech", "Tecnologia");
$revistaTech = new Revista("R001", "Tecnologia Hoje", "Vários Autores", 2023, 2, 145, 12, "Outubro");
$ebookAPI = new EBook("E001", "APIs REST com PHP", "Maria Oliveira", 2022, "PDF", 15.5, "http://biblioteca.org/ebook/e001", 10);

echo "<pre>";
echo "==========================================================\n";
echo "      CENÁRIOS OBRIGATÓRIOS DE EXECUÇÃO\n";
echo "==========================================================\n\n";


$empAna = $service->criarEmprestimo("EMP001", $alunoAna, $livroPOO, new DateTime('2026-10-01'));
echo "==========================================\n";
echo "EMPRÉSTIMO\n";
echo "==========================================\n";
echo "Usuário: " . $empAna->getUsuario()->getNome() . "\n";
echo "Tipo: Aluno\n";
echo "Material: " . $empAna->getMaterial()->getTitulo() . "\n";
echo "Data do empréstimo: " . $empAna->getDataEmprestimo()->format('d/m/Y') . "\n";
echo "Data prevista: " . $empAna->getDataPrevista()->format('d/m/Y') . "\n";
echo "Status: " . $empAna->getStatus() . "\n";
echo "==========================================\n\n";


$empCarlos = $service->criarEmprestimo("EMP002", $profCarlos, $livroPOO, new DateTime('2026-10-01'));
echo "==========================================\n";
echo "EMPRÉSTIMO PROFESSOR (COM DIAS ADICIONAIS)\n";
echo "==========================================\n";
echo "Usuário: " . $empCarlos->getUsuario()->getNome() . "\n";
echo "Tipo: Professor\n";
echo "Data do empréstimo: " . $empCarlos->getDataEmprestimo()->format('d/m/Y') . "\n";
echo "Data prevista (+7 dias prof): " . $empCarlos->getDataPrevista()->format('d/m/Y') . "\n";
echo "==========================================\n\n";

// 21.3 Devolução com Atraso
$dataDevolucaoAna = clone $empAna->getDataPrevista();
$dataDevolucaoAna->modify('+5 days');

$resDevolucao = $service->registrarDevolucao($empAna, $dataDevolucaoAna);

echo "==========================================\n";
echo "DEVOLUÇÃO\n";
echo "==========================================\n";
echo "Usuário: " . $empAna->getUsuario()->getNome() . "\n";
echo "Material: " . $empAna->getMaterial()->getTitulo() . "\n";
echo "Dias de atraso: " . $resDevolucao['diasAtraso'] . "\n";
echo "Multa: R$ " . number_format($resDevolucao['multa'], 2, ',', '.') . "\n";
echo "Status: " . $empAna->getStatus() . "\n";
echo "==========================================\n\n";

// Devolução do professor para restaurar estoque
$service->registrarDevolucao($empCarlos, $empCarlos->getDataPrevista());

// 2. SUÍTE DE TESTES OBRIGATÓRIOS
echo "==========================================================\n";
echo "      EXECUÇÃO DOS 8 TESTES OBRIGATÓRIOS\n";
echo "==========================================================\n\n";

// Teste 1: Tentar emprestar material sem estoque
echo "[TESTE 1] Material sem exemplares disponíveis:\n";
try {
    $livroEsgotado = new Livro("L002", "Livro Raro", "Autor X", 2000, 0, "111", 100, "Ed", "G");
    $service->criarEmprestimo("EMP003", $alunoAna, $livroEsgotado);
} catch (MaterialIndisponivelException $e) {
    echo " -> Sucesso (Exceção capturada): " . $e->getMessage() . "\n\n";
}

// Teste 2: Limite de 3 empréstimos para Aluno
echo "[TESTE 2] Limite de 3 empréstimos para Aluno:\n";
try {
    $alunoTeste2 = new Aluno("A002", "Pedro Rocha", "pedro@email.com");
    $l1 = new Livro("L1", "L1", "A", 2020, 5, "1", 10, "E", "G");
    $l2 = new Livro("L2", "L2", "A", 2020, 5, "2", 10, "E", "G");
    $l3 = new Livro("L3", "L3", "A", 2020, 5, "3", 10, "E", "G");
    $l4 = new Livro("L4", "L4", "A", 2020, 5, "4", 10, "E", "G");

    $service->criarEmprestimo("T2_1", $alunoTeste2, $l1);
    $service->criarEmprestimo("T2_2", $alunoTeste2, $l2);
    $service->criarEmprestimo("T2_3", $alunoTeste2, $l3);
    echo " -> Tentando o 4º empréstimo...\n";
    $service->criarEmprestimo("T2_4", $alunoTeste2, $l4);
} catch (LimiteEmprestimoException $e) {
    echo " -> Sucesso (Exceção capturada): " . $e->getMessage() . "\n\n";
}

// Teste 3: Usuário bloqueado
echo "[TESTE 3] Usuário bloqueado:\n";
try {
    $alunoBloqueado = new Aluno("A003", "Lucas Lima", "lucas@email.com");
    $alunoBloqueado->setStatus('BLOQUEADO');
    $service->criarEmprestimo("EMP004", $alunoBloqueado, $revistaTech);
} catch (UsuarioBloqueadoException $e) {
    echo " -> Sucesso (Exceção capturada): " . $e->getMessage() . "\n\n";
}

// Teste 4: Devolver empréstimo já devolvido
echo "[TESTE 4] Devolver empréstimo já devolvido:\n";
try {
    $service->registrarDevolucao($empAna);
} catch (EmprestimoInvalidoException $e) {
    echo " -> Sucesso (Exceção capturada): " . $e->getMessage() . "\n\n";
}

// Teste 5: Exceder acessos simultâneos de EBook
echo "[TESTE 5] Limite de acessos simultâneos em EBook:\n";
try {
    $ebookRestrito = new EBook("E002", "Ebook Restrito", "Autor", 2021, "PDF", 2.0, "link", 1);
    $service->criarEmprestimo("EMP_EB1", $alunoAna, $ebookRestrito);
    echo " -> 1º Acesso concedido com sucesso.\n";
    echo " -> Tentando 2º acesso simultâneo (Limite = 1)...\n";
    $service->criarEmprestimo("EMP_EB2", $profCarlos, $ebookRestrito);
} catch (MaterialIndisponivelException $e) {
    echo " -> Sucesso (Exceção capturada): " . $e->getMessage() . "\n\n";
}

// Teste 6: Devolução dentro do prazo
echo "[TESTE 6] Devolução realizada dentro do prazo:\n";
$empDentroPrazo = $service->criarEmprestimo("EMP005", $profCarlos, $revistaTech, new DateTime('2026-10-01'));
$resOk = $service->registrarDevolucao($empDentroPrazo, new DateTime('2026-10-05'));
echo " -> Dias de atraso: {$resOk['diasAtraso']} | Multa: R$ {$resOk['multa']} | Status: {$empDentroPrazo->getStatus()}\n\n";

// Teste 7: Devolução com atraso
echo "[TESTE 7] Devolução com atraso (Aluno):\n";
$alunoTeste7 = new Aluno("A004", "Beatriz Lima", "beatriz@email.com");
$empAtrasado = $service->criarEmprestimo("EMP006", $alunoTeste7, $revistaTech, new DateTime('2026-10-01'));
$resAtraso = $service->registrarDevolucao($empAtrasado, new DateTime('2026-10-15'));
echo " -> Dias de atraso: {$resAtraso['diasAtraso']} | Multa: R$ " . number_format($resAtraso['multa'], 2, ',', '.') . "\n\n";

// Teste 8: Comparativo de Multa Aluno vs Professor
echo "[TESTE 8] Comparação de valor de multa com mesmo atraso (10 dias):\n";
$multaAluno = $service->obterEstrategiaMulta($alunoAna)->calcularMulta(10);
$multaProf = $service->obterEstrategiaMulta($profCarlos)->calcularMulta(10);
echo " -> Multa Aluno (10 dias @ R$ 2,00/dia): R$ " . number_format($multaAluno, 2, ',', '.') . "\n";
echo " -> Multa Professor (10 dias @ R$ 1,00/dia): R$ " . number_format($multaProf, 2, ',', '.') . "\n\n";

// Exibição do Histórico
echo "==========================================================\n";
echo "      HISTÓRICO DE AUDITORIA E LOGS\n";
echo "==========================================================\n";
foreach ($service->getHistorico() as $log) {
    echo $log . "\n";
}
echo "</pre>";