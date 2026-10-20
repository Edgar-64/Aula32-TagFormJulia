<?php
require_once 'conexao.php';

// Adicionar pergunta (limite de 10)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enunciado'])) {
    $enunciado = trim($_POST['enunciado']);

    $total = $pdo->query("SELECT COUNT(*) FROM perguntas")->fetchColumn();

    if ($total < 10 && $enunciado !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO perguntas (enunciado, ordem) VALUES (?, ?)"
        );

        $stmt->execute([$enunciado, $total + 1]);
    }

    header('Location: gerenciar_perguntas.php');
    exit;
}

// Excluir pergunta
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);

    $pdo->prepare("DELETE FROM perguntas WHERE id = ?")
        ->execute([$id]);

    // Reorganizar a numeração após exclusão
    $perguntasRestantes = $pdo->query(
        "SELECT id FROM perguntas ORDER BY ordem ASC, id ASC"
    )->fetchAll(PDO::FETCH_COLUMN);

    $stmtOrdem = $pdo->prepare(
        "UPDATE perguntas SET ordem = ? WHERE id = ?"
    );

    foreach ($perguntasRestantes as $indice => $perguntaId) {
        $stmtOrdem->execute([$indice + 1, $perguntaId]);
    }

    header('Location: gerenciar_perguntas.php');
    exit;
}

// Buscar perguntas
$perguntas = $pdo->query(
    "SELECT * FROM perguntas ORDER BY ordem ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$totalPerguntas = count($perguntas);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Perguntas de Avaliação</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f4ff;
            color: #272449;
            font-family: Arial, sans-serif;
        }

        .pagina {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 16px;
        }

        .voltar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
            padding: 10px 14px;
            border-radius: 10px;
        }

        .card-perguntas {
            overflow: hidden;
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(28, 48, 85, 0.09);
        }

        .cabecalho {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px;
            background: #30245f;
            color: white;
        }

        .cabecalho h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .contador {
            flex-shrink: 0;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: .85rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .contador.pendente {
            background: #e9e3ff;
            color: #5638a8;
        }

        .contador.completo {
            background: #ddd6fe;
            color: #4c1d95;
        }

        .conteudo {
            padding: 24px;
        }

        .descricao {
            margin-bottom: 20px;
            color: #6b6487;
            line-height: 1.6;
        }

        .form-pergunta {
            margin-bottom: 26px;
        }

        .form-pergunta label {
            display: block;
            margin-bottom: 9px;
            font-weight: 600;
        }

        .form-pergunta .form-control {
            min-height: 48px;
            border-color: #dcd7ef;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .form-pergunta .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(40, 119, 238, .12);
        }

        .btn-adicionar {
            min-height: 48px;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .aviso {
            padding: 15px;
            border-radius: 12px;
            line-height: 1.6;
        }

        .lista-perguntas {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .item-pergunta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e7e2f5;
            border-radius: 12px;
            background: #fff;
        }

        .texto-pergunta {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .numero-pergunta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #eeebff;
            color: #4f46e5;
            font-weight: 700;
        }

        .btn-excluir {
            flex-shrink: 0;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 9px;
            font-weight: 600;
        }

        .lista-vazia {
            padding: 24px 16px;
            border: 1px dashed #c9c2e3;
            border-radius: 12px;
            color: #6b6487;
            text-align: center;
        }

        .subtitulo-lista {
            margin-bottom: 14px;
            font-size: 1rem;
            font-weight: 700;
        }


        .btn-adicionar {
            background-color: #6d28d9;
            border-color: #6d28d9;
            color: #fff;
        }

        .btn-adicionar:hover,
        .btn-adicionar:focus {
            background-color: #5b21b6;
            border-color: #5b21b6;
            color: #fff;
        }

        .btn-excluir {
            color: #6d28d9;
            border-color: #a78bfa;
        }

        .btn-excluir:hover,
        .btn-excluir:focus {
            background-color: #6d28d9;
            border-color: #6d28d9;
            color: #fff;
        }

        @media (max-width: 576px) {
            .pagina {
                padding: 16px 12px 28px;
            }

            .voltar {
                margin-bottom: 14px;
            }

            .cabecalho {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding: 20px 16px;
            }

            .cabecalho h1 {
                font-size: 1.15rem;
            }

            .conteudo {
                padding: 18px 14px;
            }

            .form-pergunta .input-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .form-pergunta .form-control {
                width: 100%;
                border-radius: 10px !important;
            }

            .btn-adicionar {
                width: 100%;
            }

            .item-pergunta {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
                padding: 14px;
            }

            .texto-pergunta {
                gap: 10px;
            }

            .btn-excluir {
                width: 100%;
                min-height: 44px;
            }
        }

        @media (max-width: 360px) {
            .conteudo {
                padding: 16px 11px;
            }

            .numero-pergunta {
                width: 30px;
                height: 30px;
            }
        }
    
        /* Componentes com acabamento visual consistente */
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; background: radial-gradient(circle at top left, #eeebff 0, #f8f7ff 42%, #f5f4ff 100%); }
        .card-perguntas { border: 1px solid #e4e0f4; border-radius: 24px; box-shadow: 0 18px 45px rgba(47,38,112,.10); }
        .cabecalho { background: linear-gradient(120deg, #30245f 0%, #4f46e5 55%, #7c3aed 100%); padding: 25px 28px; }
        .contador { border: 1px solid rgba(255,255,255,.18); box-shadow: 0 4px 12px rgba(20,15,60,.08); }
        .conteudo { padding: 28px; }
        .voltar { border: 1px solid #d4c9f7; background: rgba(255,255,255,.75); color: #5b21b6; border-radius: 999px; padding: 10px 17px; font-weight: 700; transition: .2s ease; }
        .voltar:hover, .voltar:focus { color: #fff; background: #6d28d9; border-color: #6d28d9; transform: translateY(-1px); }
        .form-pergunta .form-control { min-height: 52px; border: 1.5px solid #dcd6f0; border-radius: 14px; padding: 13px 16px; }
        .form-pergunta .form-control:focus { border-color: #8b5cf6; box-shadow: 0 0 0 4px rgba(124,58,237,.13); }
        .btn-adicionar { min-height: 52px; border: 0; border-radius: 14px; background: linear-gradient(120deg, #4f46e5, #7c3aed); box-shadow: 0 6px 14px rgba(79,70,229,.2); }
        .btn-adicionar:hover, .btn-adicionar:focus { background: linear-gradient(120deg, #4338ca, #6d28d9); box-shadow: 0 8px 18px rgba(79,70,229,.26); transform: translateY(-1px); }
        .item-pergunta { border: 1px solid #e6e0f5; border-radius: 16px; box-shadow: 0 4px 12px rgba(47,38,112,.035); transition: border-color .2s, box-shadow .2s, transform .2s; }
        .item-pergunta:hover { border-color: #c4b5fd; box-shadow: 0 8px 18px rgba(47,38,112,.07); transform: translateY(-1px); }
        .numero-pergunta { border-radius: 12px; background: linear-gradient(145deg, #eeebff, #e9ddff); color: #5b21b6; }
        .btn-excluir { border-radius: 12px; border: 1px solid #c4b5fd; background: #faf8ff; color: #6d28d9; transition: .2s ease; }
        .btn-excluir:hover, .btn-excluir:focus { background: #6d28d9; border-color: #6d28d9; color: #fff; transform: translateY(-1px); }
        .aviso { border: 1px solid #ddd6fe; background: #f3f0ff; color: #493b79; }
        .lista-vazia { background: rgba(255,255,255,.7); border-color: #c4b5fd; }
        @media (max-width: 576px) { .card-perguntas { border-radius: 18px; } .cabecalho { padding: 21px 17px; } .conteudo { padding: 22px 16px; } .voltar { border-radius: 14px; } }

    </style>
</head>

<body>

    <main class="pagina">

        <a href="index.php" class="btn btn-outline-secondary voltar">
            ← Voltar ao início
        </a>

        <section class="card card-perguntas">

            <header class="cabecalho">
                <h1>Gerenciar questões de avaliação</h1>

                <span class="contador <?= $totalPerguntas >= 10 ? 'completo' : 'pendente' ?>">
                    <?= $totalPerguntas ?> / 10 cadastradas
                </span>
            </header>

            <div class="conteudo">

                <p class="descricao">
                    Cadastre as perguntas que serão exibidas no formulário
                    de avaliação dos produtos. Você pode adicionar até
                    10 questões.
                </p>

                <?php if ($totalPerguntas < 10): ?>

                    <form
                        action="gerenciar_perguntas.php"
                        method="POST"
                        class="form-pergunta"
                    >
                        <label for="enunciado">
                            Nova pergunta
                        </label>

                        <div class="input-group">
                            <input
                                type="text"
                                name="enunciado"
                                id="enunciado"
                                class="form-control"
                                placeholder="Digite o enunciado da questão..."
                                maxlength="1000"
                                required
                            >

                            <button
                                class="btn btn-success btn-adicionar"
                                type="submit"
                            >
                                + Adicionar
                            </button>
                        </div>

                    </form>

                <?php else: ?>

                    <div class="alert alert-info aviso">
                        <strong>Limite atingido!</strong>
                        Você já cadastrou as 10 perguntas permitidas.
                        Para adicionar outra, exclua uma questão existente.
                    </div>

                <?php endif; ?>

                <h2 class="subtitulo-lista">
                    Perguntas cadastradas
                </h2>

                <?php if (empty($perguntas)): ?>

                    <div class="lista-vazia">
                        Nenhuma pergunta cadastrada ainda.
                        Adicione a primeira pergunta acima.
                    </div>

                <?php else: ?>

                    <ul class="lista-perguntas">

                        <?php foreach ($perguntas as $p): ?>

                            <li class="item-pergunta">

                                <div class="texto-pergunta">
                                    <span class="numero-pergunta">
                                        <?= (int) $p['ordem'] ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars(
                                            $p['enunciado'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </div>

                                <a
                                    href="gerenciar_perguntas.php?excluir=<?= (int) $p['id'] ?>"
                                    class="btn btn-outline-danger btn-excluir"
                                    onclick="return confirm('Tem certeza que deseja excluir esta pergunta? As avaliações relacionadas também poderão ser excluídas.');"
                                >
                                    Excluir
                                </a>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>

            </div>
        </section>

    </main>

</body>
</html>