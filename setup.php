<?php
require_once 'conexao.php';

try {
    // Tabela de Produtos
    $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        imagem VARCHAR(255),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabela de Perguntas (Até 10 questões)
    $pdo->exec("CREATE TABLE IF NOT EXISTS perguntas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        enunciado TEXT NOT NULL,
        ordem INT DEFAULT 0
    )");

    // Tabela de Respostas/Avaliações
    $pdo->exec("CREATE TABLE IF NOT EXISTS avaliacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        pergunta_id INT NOT NULL,
        nota INT NOT NULL,
        data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
        FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
    )");

    echo "<script>alert('Banco de dados e tabelas configurados com sucesso!'); window.location='index.php';</script>";
} catch (PDOException $e) {
    echo "Erro ao configurar banco: " . $e->getMessage();
}
?>