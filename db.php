<?php

declare(strict_types=1);

function getDbConnection(): PDO
{
    $dataDir = __DIR__ . "/data";
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    $dbPath = $dataDir . "/feedback.sqlite";
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    ensureFeedbackSchema($pdo);

    return $pdo;
}

function ensureFeedbackSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS feedbacks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            experiencia INTEGER NOT NULL,
            gostou TEXT NOT NULL,
            melhorar TEXT NOT NULL,
            org INTEGER NOT NULL,
            clar INTEGER NOT NULL,
            amb INTEGER NOT NULL,
            cond INTEGER NOT NULL,
            tempo INTEGER NOT NULL,
            sugestao TEXT NOT NULL,
            tema TEXT NOT NULL,
            experiencia_pessoal TEXT,
            final TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )"
    );
}

function inserirFeedback(PDO $pdo, array $dados): void
{
    $sql = "INSERT INTO feedbacks (
        experiencia,
        gostou,
        melhorar,
        org,
        clar,
        amb,
        cond,
        tempo,
        sugestao,
        tema,
        experiencia_pessoal,
        final
    ) VALUES (
        :experiencia,
        :gostou,
        :melhorar,
        :org,
        :clar,
        :amb,
        :cond,
        :tempo,
        :sugestao,
        :tema,
        :experiencia_pessoal,
        :final
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":experiencia" => (int) $dados["experiencia"],
        ":gostou" => $dados["gostou"],
        ":melhorar" => $dados["melhorar"],
        ":org" => (int) $dados["org"],
        ":clar" => (int) $dados["clar"],
        ":amb" => (int) $dados["amb"],
        ":cond" => (int) $dados["cond"],
        ":tempo" => (int) $dados["tempo"],
        ":sugestao" => $dados["sugestao"],
        ":tema" => $dados["tema"],
        ":experiencia_pessoal" => $dados["experiencia_pessoal"],
        ":final" => $dados["final"],
    ]);
}
