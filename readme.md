# Sistema de Gestão de Biblioteca e Empréstimos

## Descrição
O **Sistema de Gestão de Biblioteca e Empréstimos** é uma aplicação backend desenvolvida em PHP utilizando conceitos intermediários e avançados de Programação Orientada a Objetos (POO). O sistema automatiza o controle de acervo (livros, revistas e e-books), gestão de usuários (alunos e professores), controle de prazos de empréstimos, devoluções, cálculo de multas parametrizadas e registro em histórico/auditoria de movimentações.

---

## Requisitos
- **PHP**: Versão 8.0 ou superior
- **Servidor Web**: Apache (recomendado uso via XAMPP)
- **Editor de Código**: Visual Studio Code (ou similar)
- **Navegador Web**: Qualquer navegador moderno (Chrome, Edge, Firefox, etc.)

---

## Estrutura de Diretórios
```text
biblioteca-poo/
│
├── src/
│   ├── Models/
│   │   ├── Material.php
│   │   ├── Livro.php
│   │   ├── Revista.php
│   │   ├── EBook.php
│   │   ├── Usuario.php
│   │   ├── Aluno.php
│   │   ├── Professor.php
│   │   ├── Emprestimo.php
│   │   └── Historico.php
│   │
│   ├── Interfaces/
│   │   └── MultaInterface.php
│   │
│   ├── Multas/
│   │   ├── MultaPadrao.php
│   │   └── MultaProfessor.php
│   │
│   ├── Services/
│   │   └── EmprestimoService.php
│   │
│   └── Exceptions/
│       ├── MaterialIndisponivelException.php
│       ├── LimiteEmprestimoException.php
│       ├── UsuarioBloqueadoException.php
│       └── EmprestimoInvalidoException.php
│
├── index.php
└── README.md
```

---

## Classes Implementadas

### Modelos de Domínio (`src/Models/`)
1. **`Material` (Abstrata)**: Classe base para os itens do acervo com código, título, autor, ano de publicação, disponibilidade e quantidade de exemplares.
2. **`Livro`**: Herda de `Material`. Adiciona ISBN, número de páginas, editora e gênero. Prazo de empréstimo: 14 dias.
3. **`Revista`**: Herda de `Material`. Adiciona edição, volume e mês de publicação. Prazo de empréstimo: 7 dias.
4. **`EBook`**: Herda de `Material`. Adiciona formato, tamanho de arquivo, link de acesso e controle de acessos simultâneos (não altera estoque físico). Prazo de empréstimo: 21 dias.
5. **`Usuario` (Abstrata)**: Classe base para usuários com matrícula, nome, e-mail, telefone, status e controle de empréstimos ativos.
6. **`Aluno`**: Herda de `Usuario`. Possui limite máximo de 3 empréstimos ativos.
7. **`Professor`**: Herda de `Usuario`. Possui limite máximo de 5 empréstimos ativos e recebe 7 dias adicionais no prazo padrão do material.
8. **`Emprestimo`**: Representa a transação de empréstimo entre um `Usuario` e um `Material`, mantendo datas e status (`ATIVO`, `DEVOLVIDO`, `ATRASADO`, `CANCELADO`).
9. **`Historico`**: Registra os eventos e transações realizados no sistema com data, hora, ação, usuário e material associado.

### Interfaces e Estratégias de Multa (`src/Interfaces/` e `src/Multas/`)
10. **`MultaInterface`**: Interface que define a assinatura do método `calcularMulta(int $diasAtraso): float`.
11. **`MultaPadrao`**: Implementação da interface para alunos (R$ 2,00 por dia de atraso).
12. **`MultaProfessor`**: Implementação da interface para professores (R$ 1,00 por dia de atraso).

### Camada de Serviços (`src/Services/`)
13. **`EmprestimoService`**: Centraliza a orquestração das regras de negócio, validação de disponibilidade e limites, emissão de empréstimos, processamento de devoluções, cálculo de multas e registro no histórico.

### Exceções Personalizadas (`src/Exceptions/`)
14. **`MaterialIndisponivelException`**: Lançada quando não há estoque físico ou limite de acessos do e-book atingido.
15. **`LimiteEmprestimoException`**: Lançada quando o usuário excede a quantidade permitida de empréstimos ativos.
16. **`UsuarioBloqueadoException`**: Lançada ao tentar realizar operação com usuário de status `BLOQUEADO`.
17. **`EmprestimoInvalidoException`**: Lançada em casos de tentativa de devolução dupla ou devolução de empréstimos cancelados.

---

## Regras de Negócio
1. **Disponibilidade**: Materiais indisponíveis (estoque 0 ou limite de acessos e-book atingido) não podem ser emprestados.
2. **Limite por Tipo de Usuário**: Alunos podem ter no máximo 3 empréstimos ativos; Professores podem ter até 5.
3. **Prazos Diferenciados**:
   - Revista: 7 dias
   - Livro: 14 dias
   - E-Book: 21 dias
   - Professores recebem +7 dias de acréscimo sobre qualquer prazo padrão.
4. **Devoluções Inválidas**: Um empréstimo já devolvido ou cancelado não pode ser processado novamente.
5. **Ajuste de Estoque**: Empréstimos de materiais físicos reduzem o estoque; devoluções incrementam novamente. O estoque nunca fica negativo.
6. **Acessos Simultâneos**: E-Books controlam acessos simultâneos sem alterar estoque de exemplares físicos.
7. **Cálculo de Multas (Polimorfismo)**:
   - Multa só é calculada em caso de atraso real.
   - Aluno: R$ 2,00 por dia de atraso.
   - Professor: R$ 1,00 por dia de atraso.
8. **Bloqueio de Usuários**: Usuários com status `BLOQUEADO` não podem realizar novos empréstimos.

---

## Conceitos de POO Utilizados

- **Encapsulamento**: Atributos com visibilidade `protected` ou `private`, acessados e modificados via métodos e validadores.
- **Herança**: Reutilização de código com `Livro`, `Revista` e `EBook` estendendo `Material`, e `Aluno`/`Professor` estendendo `Usuario`.
- **Abstração**: Uso das classes abstratas `Material` e `Usuario` para definição de modelos genéricos e métodos obrigatórios (`getPrazoMaximoEmprestimo()`, `getDiasAdicionais()`).
- **Interfaces**: Definição do contrato `MultaInterface` garantindo padronização no cálculo de penalidades.
- **Polimorfismo**: Comportamentos distintos executados sob a mesma assinatura de método, como nas estratégias de multa (`MultaPadrao` vs `MultaProfessor`) e no cálculo do prazo por tipo de material.
- **Composição**: A classe `Emprestimo` é composta diretamente por instâncias de `Usuario` e `Material`.
- **Tratamento de Exceções**: Uso do bloco `try/catch/throw` utilizando exceções personalizadas estendendo a classe base `Exception`.
- **Namespaces e Autoload**: Organização das classes em namespaces coerentes com a estrutura de diretórios (`App\Models`, `App\Services`, etc.) carregados dinamicamente via `spl_autoload_register`.

---

## Instruções de Execução

1. Baixe ou copie a pasta do projeto `biblioteca-poo` para o diretório raiz do Apache no XAMPP:
   ```text
   C:\xampp\htdocs\biblioteca-poo
   ```
2. Abra o **XAMPP Control Panel** e clique em **Start** no módulo **Apache**.
3. Abra o navegador web e digite o endereço:
   ```text
   http://localhost/biblioteca-poo/index.php
   ```
4. A página executará automaticamente os cenários de uso, a suíte completa de testes e exibirá os logs do histórico de auditoria.

---

## Testes Realizados

O arquivo `index.php` executa automaticamente os 8 cenários obrigatórios de teste:

- **Teste 1**: Tentativa de empréstimo de material sem exemplares disponíveis (`MaterialIndisponivelException`).
- **Teste 2**: Tentativa de realizar o 4º empréstimo ativo para um aluno (`LimiteEmprestimoException`).
- **Teste 3**: Tentativa de realizar empréstimo para um usuário com status `BLOQUEADO` (`UsuarioBloqueadoException`).
- **Teste 4**: Tentativa de devolver um empréstimo previamente devolvido (`EmprestimoInvalidoException`).
- **Teste 5**: Tentativa de exceder o número de acessos simultâneos em um E-Book (`MaterialIndisponivelException`).
- **Teste 6**: Processamento bem-sucedido de devolução dentro do prazo (multa R$ 0,00).
- **Teste 7**: Processamento de devolução com atraso e aplicação automática de multa para aluno.
- **Teste 8**: Comparativo de cálculo de multa entre aluno e professor para o mesmo período de atraso (10 dias).