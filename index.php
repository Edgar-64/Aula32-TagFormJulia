<?php
require_once 'conexao.php';

// Cadastro de Produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['descricao'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    // Upload de Imagem Simples
    $imagem = '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem = 'uploads/' . uniqid() . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem);
    }

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, imagem) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $descricao, $imagem]);
    header('Location: index.php');
    exit;
}

// Buscar Ranking dos Produtos (Média geral das notas)
$queryRanking = $pdo->query("
    SELECT p.id, p.nome, p.imagem,
           COALESCE(AVG(a.nota), 0) as media_notas,
           COUNT(a.id) as total_avaliacoes
    FROM produtos p
    LEFT JOIN avaliacoes a ON p.id = a.produto_id
    GROUP BY p.id
    ORDER BY media_notas DESC, total_avaliacoes DESC
");
$ranking = $queryRanking->fetchAll(PDO::FETCH_ASSOC);

// Dados para o Gráfico (JSON)
$nomesProdutos = array_column($ranking, 'nome');
$mediasProdutos = array_map(fn($v) => round($v, 2), array_column($ranking, 'media_notas'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking de Produtos por Avaliação</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-primary-rgb: 79, 70, 229;
            --bs-dark: #30245f;
            --bs-dark-rgb: 48, 36, 95;
            --bs-secondary: #756b9a;
            --bs-secondary-rgb: 117, 107, 154;
            --bs-light: #f5f4ff;
            --bs-light-rgb: 245, 244, 255;
            --bs-link-color: #4f46e5;
            --bs-link-hover-color: #6d28d9;
        }

        body {
            background-color: #f5f4ff !important;
            color: #272449;
        }

        .navbar.bg-dark,
        .card-header.bg-dark {
            background-color: #30245f !important;
        }

        .card-header.bg-primary,
        .btn-primary {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #4338ca !important;
            border-color: #4338ca !important;
        }

        .btn-outline-primary {
            color: #5b21b6;
            border-color: #8b5cf6;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: #fff;
            background-color: #6d28d9;
            border-color: #6d28d9;
        }

        .text-secondary,
        .text-muted {
            color: #756b9a !important;
        }

        .bg-secondary {
            background-color: #756b9a !important;
        }

        .badge.bg-danger {
            background-color: #7c3aed !important;
        }

        .btn-outline-warning {
            color: #e9d5ff;
            border-color: #c4b5fd;
        }

        .btn-outline-warning:hover,
        .btn-outline-warning:focus {
            color: #30245f;
            background-color: #ddd6fe;
            border-color: #ddd6fe;
        }

        .form-control:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 .25rem rgba(124, 58, 237, .18);
        }
    
        /* Identidade visual refinada — sem alterar formulários ou lógica PHP */
        :root {
            --azul-profundo: #27245f;
            --azul: #4f46e5;
            --roxo: #7c3aed;
            --lilas: #c4b5fd;
            --borda: #e4e0f4;
            --fundo: #f5f4ff;
        }
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; background: radial-gradient(circle at top left, #eeebff 0, #f8f7ff 38%, #f5f4ff 100%) !important; }
        .navbar { padding: 16px 0; background: linear-gradient(115deg, #27245f, #4338a8) !important; }
        .navbar .container { gap: 14px; flex-wrap: wrap; }
        .navbar-brand { white-space: normal; letter-spacing: -.25px; }
        .navbar .btn { margin-top: 4px; margin-bottom: 4px; }
        .card { border: 1px solid rgba(228,224,244,.9) !important; border-radius: 20px !important; overflow: hidden; box-shadow: 0 12px 30px rgba(47, 38, 112, .07) !important; transition: transform .2s ease, box-shadow .2s ease; }
        .card:hover { box-shadow: 0 16px 36px rgba(47, 38, 112, .11) !important; }
        .card-header { padding: 17px 20px; border-bottom: 0; letter-spacing: .1px; }
        .card-body { padding: 22px; }
        .card-title { color: var(--azul-profundo); overflow-wrap: anywhere; }
        .card-img-top { background: #eeebff; }
        .form-label { color: #38335f; font-weight: 650; margin-bottom: 8px; }
        .form-control, .form-select { min-height: 48px; border: 1px solid #dcd6f0; border-radius: 12px; padding: 11px 14px; color: #272449; background-color: #fff; transition: border-color .2s ease, box-shadow .2s ease; }
        textarea.form-control { min-height: 112px; resize: vertical; }
        .form-control::placeholder { color: #a09ab9; }
        .form-control:focus, .form-select:focus { border-color: #8b5cf6; box-shadow: 0 0 0 4px rgba(124,58,237,.13); }
        input[type="file"].form-control { padding: 8px; }
        input[type="file"]::file-selector-button { margin: -2px 10px -2px -2px; padding: 9px 13px; border: 0; border-radius: 8px; background: #eeebff; color: #4f46e5; font-weight: 700; cursor: pointer; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; padding: 10px 16px; border-radius: 12px; font-weight: 700; letter-spacing: .05px; transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: linear-gradient(120deg, #4f46e5, #7c3aed) !important; border: 0 !important; box-shadow: 0 6px 14px rgba(79,70,229,.2); }
        .btn-primary:hover, .btn-primary:focus { background: linear-gradient(120deg, #4338ca, #6d28d9) !important; box-shadow: 0 8px 18px rgba(79,70,229,.26); }
        .btn-outline-primary { background: #f7f5ff; border: 1px solid #c4b5fd; color: #5b21b6; }
        .btn-outline-primary:hover, .btn-outline-primary:focus { background: #6d28d9; border-color: #6d28d9; color: #fff; }
        .btn-outline-light { border-color: rgba(255,255,255,.55); }
        .btn-outline-warning { color: #f0eaff; border-color: #c4b5fd; }
        .btn-outline-warning:hover, .btn-outline-warning:focus { background: #ddd6fe; border-color: #ddd6fe; color: #30245f; }
        .badge.bg-danger { background: linear-gradient(135deg, #7c3aed, #4f46e5) !important; box-shadow: 0 4px 12px rgba(79,70,229,.22); }
        .bg-secondary { background: #756b9a !important; }
        .text-secondary, .text-muted { color: #756b9a !important; }
        .container.my-4 { margin-top: 30px !important; }
        h3.fw-bold { color: #38335f !important; letter-spacing: -.4px; }
        .card .badge { padding: 9px 12px; }
        canvas { max-width: 100%; }
        @media (max-width: 576px) {
            .navbar { padding: 12px 0; }
            .navbar .container { align-items: flex-start; }
            .navbar-brand { width: 100%; font-size: 1rem; }
            .navbar .container > div { display: flex; width: 100%; flex-wrap: wrap; gap: 8px; }
            .navbar .container > div .btn { flex: 1 1 auto; margin: 0 !important; font-size: .78rem; padding: 8px 10px; }
            .container.my-4 { margin-top: 18px !important; }
            .card { border-radius: 16px !important; }
            .card-header { padding: 15px 16px; }
            .card-body { padding: 17px; }
            .btn { min-height: 44px; }
        }

    </style>

</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Sistema de Avaliação & Ranking</a>
            <div>
                <a href="gerenciar_perguntas.php" class="btn btn-outline-light btn-sm me-2">Gerenciar Perguntas (10)</a>
  
            </div>
        </div>
    </nav>

    <div class="container my-4">

        <!-- Formulário de Cadastro e Gráfico de Classificação -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">Cadastrar Novo Produto</div>
                    <div class="card-body">
                        <form action="index.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nome do Produto</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto do Produto</label>
                                <input type="file" name="imagem" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Salvar Produto</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-dark text-white fw-bold">Gráfico de Classificação dos Produtos</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; height: 300px;">
                            <canvas id="rankingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista e Posição no Ranking -->
        <h3 class="mb-3 fw-bold text-secondary">Podium e Classificação Geral</h3>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($ranking as $index => $prod): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 position-relative">
                        <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger fs-6 m-2">
                            #<?php echo $index + 1; ?>
                        </span>
                        <?php if (!empty($prod['imagem'])): ?>
                            <img src="<?php echo $prod['imagem']; ?>" class="card-img-top" alt="..." style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">Sem Imagem</div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($prod['nome']); ?></h5>
                            <p class="text-muted small mb-2">Média: <strong><?php echo number_format($prod['media_notas'], 1, ',', '.'); ?> ★</strong> (<?php echo $prod['total_avaliacoes']; ?> votos)</p>
                            <a href="avaliacao.php?produto_id=<?php echo $prod['id']; ?>" class="btn btn-outline-primary btn-sm mt-auto">Avaliar Produto 📝</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Script Chart.js -->
    <script>
        const ctx = document.getElementById('rankingChart').getContext('2d');
        const rankingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($nomesProdutos); ?>,
                datasets: [{
                    label: 'Média de Avaliação',
                    data: <?php echo json_encode($mediasProdutos); ?>,
                    backgroundColor: 'rgba(124, 58, 237, 0.72)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                }
            }
        });
    </script>
</body>
</html>