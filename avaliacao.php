<?php
require_once 'conexao.php';

$produto_id = isset($_GET['produto_id'])
    ? intval($_GET['produto_id'])
    : 0;

$stmtProd = $pdo->prepare(
    "SELECT * FROM produtos WHERE id = ?"
);
$stmtProd->execute([$produto_id]);
$produto = $stmtProd->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado. <a href='index.php'>Voltar</a>");
}

$perguntas = $pdo->query(
    "SELECT * FROM perguntas ORDER BY ordem ASC"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['notas']) && is_array($_POST['notas'])) {
        try {
            $stmtIns = $pdo->prepare(
                "INSERT INTO avaliacoes
                (produto_id, pergunta_id, nota)
                VALUES (?, ?, ?)"
            );

            foreach ($_POST['notas'] as $pergunta_id => $nota) {
                $pergunta_id = intval($pergunta_id);
                $nota = intval($nota);

                // Validar pergunta e nota
                if (
                    $nota < 1 ||
                    $nota > 5 ||
                    !in_array(
                        $pergunta_id,
                        array_column($perguntas, 'id')
                    )
                ) {
                    continue;
                }

                $stmtIns->execute([
                    $produto_id,
                    $pergunta_id,
                    $nota
                ]);
            }

            echo "<script>
                alert('Avaliação enviada com sucesso!');
                window.location.href = 'index.php';
            </script>";
            exit;

        } catch (PDOException $e) {
            die("Erro ao salvar a avaliação. Tente novamente.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Avaliar: <?= htmlspecialchars($produto['nome']) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
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
            border-radius: 10px;
            padding: 10px 14px;
        }

        .card-avaliacao {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(28, 48, 85, 0.09);
        }

        .cabecalho {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 24px;
        }

        .cabecalho h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .conteudo {
            padding: 28px;
        }

        .pergunta {
            padding: 0 0 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #e8e4f5;
        }

        .enunciado {
            display: block;
            font-size: 1.05rem;
            font-weight: 600;
            line-height: 1.6;
            margin-bottom: 16px;
            overflow-wrap: anywhere;
        }

        .opcoes-nota {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
        }

        .opcao {
            position: relative;
        }

        .opcao input {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
        }

        .opcao label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
            min-height: 70px;
            padding: 10px 4px;
            border: 2px solid #ddd8f4;
            border-radius: 13px;
            background: #fff;
            color: #625b83;
            cursor: pointer;
            transition: .2s ease;
        }

        .opcao label .estrela {
            font-size: 1.35rem;
            color: #f4b400;
        }

        .opcao label .numero {
            font-size: .85rem;
            font-weight: 700;
        }

        .opcao input:checked + label {
            background: #eeebff;
            border-color: #6366f1;
            color: #4338ca;
        }

        .opcao input:focus-visible + label {
            outline: 3px solid #c4b5fd;
            outline-offset: 3px;
        }

        .btn-enviar {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 12px;
            background: #6d28d9;
            color: white;
            font-size: 1rem;
            font-weight: 700;
            transition: .2s;
        }

        .btn-enviar:hover {
            background: #5b21b6;
            color: white;
        }

        .aviso {
            border-radius: 12px;
            padding: 16px;
            line-height: 1.6;
        }

        @media (max-width: 576px) {
            .pagina {
                padding: 16px 12px 28px;
            }

            .voltar {
                margin-bottom: 14px;
            }

            .cabecalho {
                padding: 20px 16px;
            }

            .cabecalho h1 {
                font-size: 1.2rem;
            }

            .conteudo {
                padding: 20px 15px;
            }

            .enunciado {
                font-size: .98rem;
            }

            .opcoes-nota {
                gap: 6px;
            }

            .opcao label {
                min-height: 64px;
                padding: 8px 2px;
                border-radius: 10px;
            }

            .opcao label .estrela {
                font-size: 1.2rem;
            }

            .opcao label .numero {
                font-size: .8rem;
            }

            .btn-enviar {
                min-height: 54px;
            }
        }

        @media (max-width: 360px) {
            .conteudo {
                padding: 18px 11px;
            }

            .opcoes-nota {
                gap: 4px;
            }

            .opcao label {
                min-height: 58px;
            }

            .opcao label .estrela {
                font-size: 1.05rem;
            }
        }
    
        /* Componentes com acabamento visual consistente */
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; background: radial-gradient(circle at top left, #eeebff 0, #f8f7ff 42%, #f5f4ff 100%); }
        .card-avaliacao { border: 1px solid #e4e0f4; border-radius: 24px; box-shadow: 0 18px 45px rgba(47,38,112,.10); }
        .cabecalho { background: linear-gradient(120deg, #30245f 0%, #4f46e5 55%, #7c3aed 100%); padding: 28px; }
        .cabecalho h1 { letter-spacing: -.35px; }
        .conteudo { padding: 30px; }
        .voltar { border: 1px solid #d4c9f7; background: rgba(255,255,255,.75); color: #5b21b6; border-radius: 999px; padding: 10px 17px; font-weight: 700; transition: .2s ease; }
        .voltar:hover, .voltar:focus { color: #fff; background: #6d28d9; border-color: #6d28d9; transform: translateY(-1px); }
        .pergunta { border-bottom-color: #e6e0f5; }
        .enunciado { color: #302b58; }
        .opcao label { border: 1.5px solid #ddd6f5; border-radius: 16px; background: linear-gradient(180deg, #fff, #fbfaff); box-shadow: 0 3px 10px rgba(47,38,112,.035); }
        .opcao label:hover { border-color: #a78bfa; background: #f7f4ff; transform: translateY(-2px); box-shadow: 0 7px 16px rgba(79,70,229,.09); }
        .opcao input:checked + label { background: linear-gradient(145deg, #eeebff, #e9ddff); border-color: #7c3aed; color: #4c1d95; box-shadow: 0 0 0 3px rgba(124,58,237,.10); }
        .btn-enviar { border-radius: 14px; background: linear-gradient(120deg, #4f46e5, #7c3aed); box-shadow: 0 8px 18px rgba(79,70,229,.22); }
        .btn-enviar:hover, .btn-enviar:focus { background: linear-gradient(120deg, #4338ca, #6d28d9); box-shadow: 0 10px 22px rgba(79,70,229,.28); transform: translateY(-1px); }
        .aviso { border: 1px solid #ddd6fe; background: #f3f0ff; color: #493b79; }
        .aviso a { color: #5b21b6; font-weight: 700; }
        @media (max-width: 576px) { .card-avaliacao { border-radius: 18px; } .cabecalho { padding: 22px 18px; } .conteudo { padding: 22px 16px; } .voltar { border-radius: 14px; } }

    </style>
</head>

<body>
    <main class="pagina">

        <a href="index.php" class="btn btn-outline-secondary voltar">
            ← Voltar ao início
        </a>

        <section class="card card-avaliacao">

            <header class="cabecalho">
                <h1>
                    Avaliação do produto:
                    <?= htmlspecialchars($produto['nome']) ?>
                </h1>
            </header>

            <div class="conteudo">

                <?php if (empty($perguntas)): ?>

                    <div class="alert alert-warning aviso">
                        Nenhuma pergunta cadastrada no sistema.
                        Cadastre as questões primeiro em
                        <a href="gerenciar_perguntas.php">
                            Gerenciar Perguntas
                        </a>.
                    </div>

                <?php else: ?>

                    <p class="text-secondary mb-4">
                        Responda às perguntas abaixo selecionando
                        uma nota de 1 a 5 estrelas.
                    </p>

                    <form
                        action="avaliacao.php?produto_id=<?= $produto_id ?>"
                        method="POST"
                    >

                        <?php foreach ($perguntas as $index => $p): ?>

                            <fieldset class="pergunta">

                                <legend class="enunciado">
                                    Q<?= $index + 1 ?>.
                                    <?= htmlspecialchars($p['enunciado']) ?>
                                </legend>

                                <div class="opcoes-nota">

                                    <?php for ($i = 1; $i <= 5; $i++): ?>

                                        <div class="opcao">

                                            <input
                                                type="radio"
                                                name="notas[<?= $p['id'] ?>]"
                                                id="q<?= $p['id'] ?>_<?= $i ?>"
                                                value="<?= $i ?>"
                                                required
                                            >

                                            <label for="q<?= $p['id'] ?>_<?= $i ?>">
                                                <span class="estrela"
                                                    aria-hidden="true">★</span>
                                                <span class="numero">
                                                    <?= $i ?>
                                                </span>
                                            </label>

                                        </div>

                                    <?php endfor; ?>

                                </div>

                            </fieldset>

                        <?php endforeach; ?>

                        <button
                            type="submit"
                            class="btn btn-enviar"
                        >
                            Enviar avaliação 🚀
                        </button>

                    </form>

                <?php endif; ?>

            </div>
        </section>

    </main>
</body>
</html>