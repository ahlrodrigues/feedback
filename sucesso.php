<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envio Concluído</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f6fbff;
            color: #1f3c5a;
        }

        .box {
            width: min(560px, 92vw);
            background: #fff;
            border: 1px solid #a5d6a7;
            border-radius: 10px;
            padding: 22px;
            box-sizing: border-box;
        }

        h1 {
            margin: 0 0 10px;
            color: #1b5e20;
            font-size: 26px;
        }

        p {
            margin: 0 0 18px;
            font-size: 16px;
            line-height: 1.45;
        }

        a {
            display: inline-block;
            text-decoration: none;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            padding: 10px 14px;
        }

        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Mensagem enviada com sucesso!</h1>
        <p>Obrigado pelo seu feedback anônimo. Sua resposta foi registrada.</p>
        <?php if (isset($_GET["email"]) && $_GET["email"] === "0"): ?>
            <p style="color:#b26a00;"><strong>Aviso:</strong> o e-mail de notificação não foi enviado, mas sua resposta foi salva.</p>
        <?php endif; ?>
    </div>
</body>
</html>
