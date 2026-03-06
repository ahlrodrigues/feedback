<?php
require_once __DIR__ . "/db.php";

session_start();

function esc(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
}

function carregarCredenciaisPainel(): array
{
    $email = "admin@feedback.local";
    $senhaHashPadrao = '$2y$12$EfASsD0sMi9xdpPEdgJ4peB536Mi5mtoQbSMMck/wS9V3P0l/92se';
    $senhaHash = $senhaHashPadrao;
    $senhaTextoLegado = "";

    $envPath = __DIR__ . "/deploy.env";
    if (is_file($envPath)) {
        $linhas = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($linhas)) {
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if ($linha === "" || str_starts_with($linha, "#") || strpos($linha, "=") === false) {
                    continue;
                }

                [$chave, $valor] = explode("=", $linha, 2);
                $chave = trim($chave);
                $valor = trim($valor);

                if ($chave === "PAINEL_EMAIL" && $valor !== "") {
                    $email = $valor;
                }

                if ($chave === "PAINEL_SENHA_HASH" && $valor !== "") {
                    $senhaHash = $valor;
                }

                if ($chave === "PAINEL_SENHA" && $valor !== "") {
                    $senhaTextoLegado = $valor;
                }
            }
        }
    }

    if ($senhaTextoLegado !== "") {
        $senhaHash = password_hash($senhaTextoLegado, PASSWORD_DEFAULT);
    }

    return ["email" => $email, "senha_hash" => $senhaHash];
}

$credenciais = carregarCredenciaisPainel();
$erroLogin = "";

if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();
    header("Location: painel.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "login") {
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $senha = isset($_POST["senha"]) ? (string) $_POST["senha"] : "";

    if (hash_equals($credenciais["email"], $email) && password_verify($senha, $credenciais["senha_hash"])) {
        session_regenerate_id(true);
        $_SESSION["painel_autenticado"] = true;
        header("Location: painel.php");
        exit();
    }

    $erroLogin = "E-mail ou senha inválidos.";
}

$autenticado = isset($_SESSION["painel_autenticado"]) && $_SESSION["painel_autenticado"] === true;

if (!$autenticado):
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Painel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f3f7fb;
            color: #13283d;
        }

        .login-box {
            width: min(420px, 92vw);
            background: #fff;
            border: 1px solid #d7e3ef;
            border-radius: 10px;
            padding: 18px;
        }

        h1 {
            margin: 0 0 14px;
            font-size: 22px;
        }

        label {
            display: block;
            margin: 8px 0 6px;
            font-weight: 600;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #c6d8ea;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
        }

        button {
            margin-top: 8px;
            width: 100%;
            border: none;
            border-radius: 6px;
            padding: 10px 12px;
            background: #1f77b4;
            color: #fff;
            cursor: pointer;
        }

        .erro {
            color: #b71c1c;
            background: #ffebee;
            border: 1px solid #ef9a9a;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <form class="login-box" method="POST" action="painel.php">
        <h1>Painel de Feedbacks</h1>
        <?php if ($erroLogin !== ""): ?>
            <div class="erro"><?php echo esc($erroLogin); ?></div>
        <?php endif; ?>
        <input type="hidden" name="acao" value="login">

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
<?php
exit();
endif;

if (!isset($_SESSION["painel_csrf"]) || !is_string($_SESSION["painel_csrf"])) {
    $_SESSION["painel_csrf"] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION["painel_csrf"];
$erroAcao = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "excluir") {
    $tokenRecebido = isset($_POST["csrf_token"]) ? (string) $_POST["csrf_token"] : "";
    $idExclusao = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

    if (!hash_equals($csrfToken, $tokenRecebido)) {
        $erroAcao = "Não foi possível validar a ação de exclusão.";
    } elseif ($idExclusao <= 0) {
        $erroAcao = "ID da resposta inválido.";
    } else {
        try {
            $pdoDelete = getDbConnection();
            $stmtDelete = $pdoDelete->prepare("DELETE FROM feedbacks WHERE id = :id");
            $stmtDelete->execute([":id" => $idExclusao]);

            if ($stmtDelete->rowCount() > 0) {
                $_SESSION["painel_flash"] = "Resposta #{$idExclusao} excluída com sucesso.";
            } else {
                $_SESSION["painel_flash"] = "Nenhuma resposta encontrada para o ID #{$idExclusao}.";
            }
            header("Location: painel.php");
            exit();
        } catch (Throwable $e) {
            $erroAcao = "Falha ao excluir a resposta.";
        }
    }
}

$rotulosNotas = [
    1 => "Precisa Melhorar",
    2 => "Regular",
    3 => "Bom",
    4 => "Ótimo",
    5 => "Maravilhoso!",
];

$camposEstrela = [
    "experiencia" => "Experiência geral",
    "org" => "Organização geral",
    "clar" => "Clareza dos conteúdos",
    "amb" => "Ambiente fraterno",
    "cond" => "Condução dos facilitadores",
    "tempo" => "Tempo das aulas",
];

function obterDistribuicao(PDO $pdo, string $campo): array
{
    $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

    $stmt = $pdo->query("SELECT {$campo} AS nota, COUNT(*) AS total FROM feedbacks GROUP BY {$campo}");
    foreach ($stmt as $row) {
        $nota = (int) $row["nota"];
        if (isset($dist[$nota])) {
            $dist[$nota] = (int) $row["total"];
        }
    }

    return $dist;
}

$totalRespostas = 0;
$medias = [];
$distribuicoes = [];
$ultimasRespostas = [];
$erro = "";

try {
    $pdo = getDbConnection();

    $totalRespostas = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks")->fetchColumn();

    $sqlMedias = "SELECT
        AVG(experiencia) AS experiencia,
        AVG(org) AS org,
        AVG(clar) AS clar,
        AVG(amb) AS amb,
        AVG(cond) AS cond,
        AVG(tempo) AS tempo
    FROM feedbacks";
    $medias = $pdo->query($sqlMedias)->fetch() ?: [];

    foreach (array_keys($camposEstrela) as $campo) {
        $distribuicoes[$campo] = obterDistribuicao($pdo, $campo);
    }

    $stmtUltimas = $pdo->query(
        "SELECT
            id,
            created_at,
            gostou,
            melhorar,
            sugestao,
            tema,
            experiencia_pessoal,
            final
        FROM feedbacks
        ORDER BY id DESC
        LIMIT 20"
    );
    $ultimasRespostas = $stmtUltimas->fetchAll();
} catch (Throwable $e) {
    $erro = "Não foi possível carregar os dados do painel.";
}

$labelsGrafico = array_values($camposEstrela);
$datasetsDistribuicao = [];
$coresNotas = ["#c62828", "#ef6c00", "#f9a825", "#7cb342", "#2e7d32"];
for ($nota = 1; $nota <= 5; $nota++) {
    $dadosNota = [];
    foreach (array_keys($camposEstrela) as $campo) {
        $distAtual = $distribuicoes[$campo] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $dadosNota[] = (int) ($distAtual[$nota] ?? 0);
    }

    $datasetsDistribuicao[] = [
        "label" => $rotulosNotas[$nota],
        "data" => $dadosNota,
        "backgroundColor" => $coresNotas[$nota - 1],
    ];
}

$labelsRadar = array_values($camposEstrela);
$dadosRadar = [];
foreach (array_keys($camposEstrela) as $campo) {
    $dadosRadar[] = isset($medias[$campo]) ? round((float) $medias[$campo], 2) : 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Feedbacks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f3f7fb;
            color: #13283d;
        }

        .container {
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 16px 24px;
        }

        .topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0;
        }

        .logout {
            text-decoration: none;
            border: 1px solid #b9cde0;
            background: #fff;
            color: #13283d;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 14px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .card {
            background: #fff;
            border: 1px solid #d7e3ef;
            border-radius: 8px;
            padding: 10px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
        }

        .card-titulo {
            line-height: 1.2;
            min-height: 40px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .card strong {
            display: block;
            font-size: 36px;
            line-height: 1.1;
            margin: 6px 0 2px;
        }

        .card-media {
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .box {
            background: #fff;
            border: 1px solid #d7e3ef;
            border-radius: 8px;
            padding: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            text-align: left;
            border-bottom: 1px solid #e4edf6;
            padding: 8px 6px;
            vertical-align: top;
        }

        .respostas {
            margin-top: 16px;
        }

        .resposta-item {
            background: #fff;
            border: 1px solid #d7e3ef;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .erro {
            color: #b71c1c;
            background: #ffebee;
            border: 1px solid #ef9a9a;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 16px;
        }

        .sucesso {
            color: #1b5e20;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 16px;
        }

        .resposta-topo {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 4px;
        }

        .btn-excluir {
            border: 1px solid #ef9a9a;
            background: #fff;
            color: #b71c1c;
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .cards {
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="topo">
        <h1>Painel de Feedbacks</h1>
        <a class="logout" href="painel.php?logout=1">Sair</a>
    </div>

    <?php if (isset($_SESSION["painel_flash"]) && is_string($_SESSION["painel_flash"])): ?>
        <div class="sucesso"><?php echo esc($_SESSION["painel_flash"]); ?></div>
        <?php unset($_SESSION["painel_flash"]); ?>
    <?php endif; ?>

    <?php if ($erroAcao !== ""): ?>
        <div class="erro"><?php echo esc($erroAcao); ?></div>
    <?php endif; ?>

    <?php if ($erro !== ""): ?>
        <div class="erro"><?php echo esc($erro); ?></div>
    <?php else: ?>
        <div class="cards">
            <div class="card">
                <div>Total de respostas</div>
                <strong><?php echo $totalRespostas; ?></strong>
            </div>
            <?php foreach ($camposEstrela as $campo => $titulo): ?>
                <div class="card">
                    <div class="card-titulo"><?php echo esc($titulo); ?></div>
                    <strong><?php echo number_format((float) ($medias[$campo] ?? 0), 2, ",", "."); ?></strong>
                    <div class="card-media">Média</div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="grid">
            <div class="box">
                <h2>Distribuição das notas (estrelas)</h2>
                <canvas id="graficoDistribuicao" height="220"></canvas>
            </div>
            <div class="box">
                <h2>Média por critério</h2>
                <canvas id="graficoMedias" height="220"></canvas>
                <table>
                    <thead>
                        <tr>
                            <th>Critério</th>
                            <th>Média</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($camposEstrela as $campo => $titulo): ?>
                        <tr>
                            <td><?php echo esc($titulo); ?></td>
                            <td><?php echo number_format((float) ($medias[$campo] ?? 0), 2, ",", "."); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="respostas">
            <h2>Últimas 20 respostas textuais</h2>
            <?php if (count($ultimasRespostas) === 0): ?>
                <div class="box">Nenhuma resposta registrada ainda.</div>
            <?php else: ?>
                <?php foreach ($ultimasRespostas as $resposta): ?>
                    <div class="resposta-item">
                        <div class="resposta-topo">
                            <div><strong>#<?php echo (int) $resposta["id"]; ?></strong> - <?php echo esc($resposta["created_at"]); ?></div>
                            <form method="POST" action="painel.php" onsubmit="return confirm('Deseja realmente excluir a resposta #<?php echo (int) $resposta["id"]; ?>?');">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?php echo (int) $resposta["id"]; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo esc($csrfToken); ?>">
                                <button class="btn-excluir" type="submit">Excluir</button>
                            </form>
                        </div>
                        <div><strong>O que mais gostou:</strong> <?php echo esc($resposta["gostou"]); ?></div>
                        <div><strong>O que pode melhorar:</strong> <?php echo esc($resposta["melhorar"]); ?></div>
                        <div><strong>Sugestões:</strong> <?php echo esc($resposta["sugestao"]); ?></div>
                        <div><strong>Tema para aprofundar:</strong> <?php echo esc($resposta["tema"]); ?></div>
                        <div><strong>Experiência pessoal:</strong> <?php echo esc($resposta["experiencia_pessoal"] !== "" ? $resposta["experiencia_pessoal"] : "Não informada"); ?></div>
                        <div><strong>Mensagem final:</strong> <?php echo esc($resposta["final"]); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labelsDistribuicao = <?php echo json_encode($labelsGrafico, JSON_UNESCAPED_UNICODE); ?>;
const datasetsDistribuicao = <?php echo json_encode($datasetsDistribuicao, JSON_UNESCAPED_UNICODE); ?>;
const labelsRadar = <?php echo json_encode($labelsRadar, JSON_UNESCAPED_UNICODE); ?>;
const dadosRadar = <?php echo json_encode($dadosRadar, JSON_UNESCAPED_UNICODE); ?>;

const ctxDistribuicao = document.getElementById('graficoDistribuicao');
if (ctxDistribuicao) {
    new Chart(ctxDistribuicao, {
        type: 'bar',
        data: {
            labels: labelsDistribuicao,
            datasets: datasetsDistribuicao
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

const ctxMedias = document.getElementById('graficoMedias');
if (ctxMedias) {
    new Chart(ctxMedias, {
        type: 'radar',
        data: {
            labels: labelsRadar,
            datasets: [{
                label: 'Média',
                data: dadosRadar,
                borderColor: '#1f77b4',
                backgroundColor: 'rgba(31, 119, 180, 0.20)',
                pointBackgroundColor: '#1f77b4'
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 5,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}
</script>
</body>
</html>
