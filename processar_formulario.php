<?php
require_once __DIR__ . "/db.php";

function redirecionarSucesso(bool $emailEnviado): void
{
    if (is_file(__DIR__ . "/sucesso.php")) {
        $sufixo = $emailEnviado ? "" : "?email=0";
        header("Location: sucesso.php{$sufixo}");
        exit();
    }

    // Fallback seguro caso sucesso.php nao tenha sido publicado no servidor.
    header("Content-Type: text/html; charset=UTF-8");
    $mensagem = $emailEnviado
        ? "Mensagem enviada com sucesso!"
        : "Resposta registrada com sucesso! (Aviso: o envio de e-mail falhou.)";
    echo "<!DOCTYPE html><html lang=\"pt-BR\"><head><meta charset=\"UTF-8\"><title>Sucesso</title></head><body>";
    echo "<h1>{$mensagem}</h1><p><a href=\"formulario_contato.php\">Voltar ao formulário</a></p>";
    echo "</body></html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: formulario_contato.php");
    exit();
}

$camposObrigatorios = [
   "experiencia",
    "gostou",
    "melhorar",
    "org",
    "clar",
    "amb",
    "cond",
    "tempo",
    "sugestao",
    "tema",
    "final",
];

foreach ($camposObrigatorios as $campo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === "") {
        header("Location: formulario_contato.php?status=error");
        exit();
    }
}

$experiencia = trim($_POST["experiencia"]);
$gostou = trim($_POST["gostou"]);
$melhorar = trim($_POST["melhorar"]);
$org = trim($_POST["org"]);
$clar = trim($_POST["clar"]);
$amb = trim($_POST["amb"]);
$cond = trim($_POST["cond"]);
$tempo = trim($_POST["tempo"]);
$sugestao = trim($_POST["sugestao"]);
$tema = trim($_POST["tema"]);
$experienciaPessoal = isset($_POST["experiencia_pessoal"]) ? trim($_POST["experiencia_pessoal"]) : "";
$final = trim($_POST["final"]);

$camposEstrela = [$experiencia, $org, $clar, $amb, $cond, $tempo];
foreach ($camposEstrela as $nota) {
    if (!in_array($nota, ["1", "2", "3", "4", "5"], true)) {
        header("Location: formulario_contato.php?status=error");
        exit();
    }
}

$mapaAvaliacao = [
    "1" => "Precisa Melhorar",
    "2" => "Regular",
    "3" => "Bom",
    "4" => "Ótimo",
    "5" => "Maravilhoso!",
];

$textoOuOriginal = static function (string $valor) use ($mapaAvaliacao): string {
    return $mapaAvaliacao[$valor] ?? $valor;
};

$experienciaTexto = $textoOuOriginal($experiencia);
$orgTexto = $textoOuOriginal($org);
$clarTexto = $textoOuOriginal($clar);
$ambTexto = $textoOuOriginal($amb);
$condTexto = $textoOuOriginal($cond);
$tempoTexto = $textoOuOriginal($tempo);

$esc = static function (string $valor): string {
    return nl2br(htmlspecialchars($valor, ENT_QUOTES, "UTF-8"));
};

try {
    $pdo = getDbConnection();
    inserirFeedback($pdo, [
        "experiencia" => $experiencia,
        "gostou" => $gostou,
        "melhorar" => $melhorar,
        "org" => $org,
        "clar" => $clar,
        "amb" => $amb,
        "cond" => $cond,
        "tempo" => $tempo,
        "sugestao" => $sugestao,
        "tema" => $tema,
        "experiencia_pessoal" => $experienciaPessoal,
        "final" => $final,
    ]);
} catch (Throwable $e) {
    header("Location: formulario_contato.php?status=error");
    exit();
}

$destinatario = "cursodirigentes26@gmail.com";
$assunto = "Feedbak Anônimo do Curso de Formação de Dirigentes - 2026";
$corpoEmail = "<!DOCTYPE html><html lang=\"pt-BR\"><body>";
$corpoEmail .= "<p><strong>Feedbak Anônimo do Curso de Formação de Dirigentes - 2026</strong></p>";
$corpoEmail .= "<p><strong>1. Experiência geral:</strong> " . $esc($experienciaTexto) . "</p>";
$corpoEmail .= "<p><strong>2. O que mais gostou:</strong><br>" . $esc($gostou) . "</p>";
$corpoEmail .= "<p><strong>3. O que pode melhorar:</strong><br>" . $esc($melhorar) . "</p>";
$corpoEmail .= "<p><strong>4. Avaliação por aspectos:</strong><br>";
$corpoEmail .= "Organização das aulas: " . $esc($orgTexto) . "<br>";
$corpoEmail .= "Clareza dos conteúdos: " . $esc($clarTexto) . "<br>";
$corpoEmail .= "Ambiente fraterno: " . $esc($ambTexto) . "<br>";
$corpoEmail .= "Condução dos facilitadores: " . $esc($condTexto) . "<br>";
$corpoEmail .= "Tempo das aulas: " . $esc($tempoTexto) . "</p>";
$corpoEmail .= "<p><strong>5. Sugestões para melhorar:</strong><br>" . $esc($sugestao) . "</p>";
$corpoEmail .= "<p><strong>6. Tema para aprofundar:</strong><br>" . $esc($tema) . "</p>";
$corpoEmail .= "<p><strong>7. Experiência pessoal (opcional):</strong><br>" . $esc($experienciaPessoal !== "" ? $experienciaPessoal : "Não informada") . "</p>";
$corpoEmail .= "<p><strong>8. Mensagem final:</strong><br>" . $esc($final) . "</p>";
$corpoEmail .= "</body></html>";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8";

$enviado = mail($destinatario, $assunto, $corpoEmail, $headers);
redirecionarSucesso($enviado);
?>
