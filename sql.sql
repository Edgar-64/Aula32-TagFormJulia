CREATE DATABASE IF NOT EXISTS sistema_avaliacao
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sistema_avaliacao;

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de perguntas
CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enunciado TEXT NOT NULL,
    ordem INT DEFAULT 0
);

-- Tabela de avaliações e respostas
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    nota INT NOT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_avaliacoes_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_avaliacoes_pergunta
        FOREIGN KEY (pergunta_id)
        REFERENCES perguntas(id)
        ON DELETE CASCADE
);