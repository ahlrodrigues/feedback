<?php
$status = isset($_GET["status"]) ? $_GET["status"] : "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbak Anônimo do Curso de Formação de Dirigentes - 2026</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6fbff;
        }

        form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        h1 {
            max-width: 800px;
            margin: 20px auto 0;
            padding: 0 6px;
            font-size: 24px;
            color: #1f3c5a;
        }

        label,
        input,
        textarea,
        select {
            display: block;
            margin-bottom: 10px;
            width: 100%;
        }

        input,
        textarea,
        select {
            padding: 8px;
            border-radius: 3px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        .q {
            border: 1px solid #d9e4f2;
            border-radius: 8px;
            background: #fff;
            padding: 12px;
            margin-bottom: 14px;
        }

        .q h2 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        .pergunta-com-estrelas {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .pergunta-com-estrelas > label,
        .pergunta-com-estrelas > h2 {
            width: auto;
            margin: 0;
            flex: 1 1 280px;
        }

        .escala {
            width: 420px;
            max-width: 100%;
            flex: 0 0 auto;
        }

        .escala-legenda {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin-bottom: 4px;
            font-size: 11px;
            color: #38516d;
            gap: 0;
        }

        .escala-legenda span {
            text-align: center;
            line-height: 1.1;
        }

        .stars {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: space-between;
            width: 100%;
            gap: 0;
            margin-bottom: 0;
        }

        .stars input {
            display: none;
        }

        .stars label {
            position: relative;
            width: 20%;
            text-align: center;
            margin: 0;
            font-size: 30px;
            line-height: 1;
            cursor: pointer;
            color: #c7c7c7;
        }

        .stars label::before {
            content: "★";
        }

        .stars label::after {
            content: attr(data-label);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 8px);
            transform: translateX(-50%);
            background: #24374d;
            color: #fff;
            font-size: 11px;
            line-height: 1;
            padding: 5px 7px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease;
        }

        .stars label:hover::after {
            opacity: 1;
        }

        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: #ffb300;
        }

        .avaliacao-item {
            margin-bottom: 14px;
        }

        @media (max-width: 860px) {
            .pergunta-com-estrelas {
                flex-direction: column;
            }

            .pergunta-com-estrelas > label,
            .pergunta-com-estrelas > h2 {
                margin-bottom: 8px;
            }
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .status {
            padding: 10px 12px;
            border-radius: 5px;
            margin-bottom: 14px;
            border: 1px solid transparent;
        }

        .status-success {
            background-color: #e8f5e9;
            color: #1b5e20;
            border-color: #a5d6a7;
        }

        .status-error {
            background-color: #ffebee;
            color: #b71c1c;
            border-color: #ef9a9a;
        }

        .required-note {
            margin: 0 0 12px;
            color: #38516d;
            font-size: 13px;
        }

        .required-mark {
            color: #b71c1c;
            font-weight: 700;
        }
    </style>
</head>
<body>

<h1>Feedbak Anônimo do Curso de Formação de Dirigentes - 2026</h1>

<form action="processar_formulario.php" method="POST">
    <?php if ($status === "error"): ?>
        <div class="status status-error">Não foi possível enviar. Verifique os campos e tente novamente.</div>
    <?php endif; ?>
    <p class="required-note"><span class="required-mark">*</span> Campo obrigatório</p>

    <section class="q">
        <div class="pergunta-com-estrelas">
            <h2>1. Como você avalia sua experiência geral no curso? <span class="required-mark">*</span></h2>
            <div class="escala">
                <div class="escala-legenda">
                    <span>Precisa Melhorar</span>
                    <span>Regular</span>
                    <span>Bom</span>
                    <span>Ótimo</span>
                    <span>Maravilhoso!</span>
                </div>
                <div class="stars" role="radiogroup" aria-label="Avaliação da experiência geral">
                    <input type="radio" id="experiencia-5" name="experiencia" value="5" required>
                    <label for="experiencia-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                    <input type="radio" id="experiencia-4" name="experiencia" value="4">
                    <label for="experiencia-4" title="Ótimo" data-label="Ótimo"></label>
                    <input type="radio" id="experiencia-3" name="experiencia" value="3">
                    <label for="experiencia-3" title="Bom" data-label="Bom"></label>
                    <input type="radio" id="experiencia-2" name="experiencia" value="2">
                    <label for="experiencia-2" title="Regular" data-label="Regular"></label>
                    <input type="radio" id="experiencia-1" name="experiencia" value="1">
                    <label for="experiencia-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                </div>
            </div>
        </div>
    </section>

    <section class="q">
        <h2>2. O que você mais gostou? <span class="required-mark">*</span></h2>
        <textarea id="gostou" name="gostou" required></textarea>
    </section>

    <section class="q">
        <h2>3. O que poderia ser melhorado? <span class="required-mark">*</span></h2>
        <textarea id="melhorar" name="melhorar" required></textarea>
    </section>

    <section class="q">
        <h2>4. Avaliação por aspectos <span class="required-mark">*</span></h2>

        <div class="avaliacao-item">
            <div class="pergunta-com-estrelas">
                <label>Organização geral: <span class="required-mark">*</span></label>
                <div class="escala">
                    <div class="escala-legenda">
                        <span>Precisa Melhorar</span>
                        <span>Regular</span>
                        <span>Bom</span>
                        <span>Ótimo</span>
                        <span>Maravilhoso!</span>
                    </div>
                    <div class="stars" role="radiogroup" aria-label="Organização das aulas">
                        <input type="radio" id="org-5" name="org" value="5" required>
                        <label for="org-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                        <input type="radio" id="org-4" name="org" value="4">
                        <label for="org-4" title="Ótimo" data-label="Ótimo"></label>
                        <input type="radio" id="org-3" name="org" value="3">
                        <label for="org-3" title="Bom" data-label="Bom"></label>
                        <input type="radio" id="org-2" name="org" value="2">
                        <label for="org-2" title="Regular" data-label="Regular"></label>
                        <input type="radio" id="org-1" name="org" value="1">
                        <label for="org-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="avaliacao-item">
            <div class="pergunta-com-estrelas">
                <label>Clareza dos conteúdos: <span class="required-mark">*</span></label>
                <div class="escala">
                    <div class="escala-legenda">
                        <span>Precisa Melhorar</span>
                        <span>Regular</span>
                        <span>Bom</span>
                        <span>Ótimo</span>
                        <span>Maravilhoso!</span>
                    </div>
                    <div class="stars" role="radiogroup" aria-label="Clareza dos conteúdos">
                        <input type="radio" id="clar-5" name="clar" value="5" required>
                        <label for="clar-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                        <input type="radio" id="clar-4" name="clar" value="4">
                        <label for="clar-4" title="Ótimo" data-label="Ótimo"></label>
                        <input type="radio" id="clar-3" name="clar" value="3">
                        <label for="clar-3" title="Bom" data-label="Bom"></label>
                        <input type="radio" id="clar-2" name="clar" value="2">
                        <label for="clar-2" title="Regular" data-label="Regular"></label>
                        <input type="radio" id="clar-1" name="clar" value="1">
                        <label for="clar-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="avaliacao-item">
            <div class="pergunta-com-estrelas">
                <label>Ambiente fraterno: <span class="required-mark">*</span></label>
                <div class="escala">
                    <div class="escala-legenda">
                        <span>Precisa Melhorar</span>
                        <span>Regular</span>
                        <span>Bom</span>
                        <span>Ótimo</span>
                        <span>Maravilhoso!</span>
                    </div>
                    <div class="stars" role="radiogroup" aria-label="Ambiente fraterno">
                        <input type="radio" id="amb-5" name="amb" value="5" required>
                        <label for="amb-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                        <input type="radio" id="amb-4" name="amb" value="4">
                        <label for="amb-4" title="Ótimo" data-label="Ótimo"></label>
                        <input type="radio" id="amb-3" name="amb" value="3">
                        <label for="amb-3" title="Bom" data-label="Bom"></label>
                        <input type="radio" id="amb-2" name="amb" value="2">
                        <label for="amb-2" title="Regular" data-label="Regular"></label>
                        <input type="radio" id="amb-1" name="amb" value="1">
                        <label for="amb-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="avaliacao-item">
            <div class="pergunta-com-estrelas">
                <label>Condução dos facilitadores: <span class="required-mark">*</span></label>
                <div class="escala">
                    <div class="escala-legenda">
                        <span>Precisa Melhorar</span>
                        <span>Regular</span>
                        <span>Bom</span>
                        <span>Ótimo</span>
                        <span>Maravilhoso!</span>
                    </div>
                    <div class="stars" role="radiogroup" aria-label="Condução dos facilitadores">
                        <input type="radio" id="cond-5" name="cond" value="5" required>
                        <label for="cond-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                        <input type="radio" id="cond-4" name="cond" value="4">
                        <label for="cond-4" title="Ótimo" data-label="Ótimo"></label>
                        <input type="radio" id="cond-3" name="cond" value="3">
                        <label for="cond-3" title="Bom" data-label="Bom"></label>
                        <input type="radio" id="cond-2" name="cond" value="2">
                        <label for="cond-2" title="Regular" data-label="Regular"></label>
                        <input type="radio" id="cond-1" name="cond" value="1">
                        <label for="cond-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="avaliacao-item">
            <div class="pergunta-com-estrelas">
                <label>Tempo das aulas: <span class="required-mark">*</span></label>
                <div class="escala">
                    <div class="escala-legenda">
                        <span>Precisa Melhorar</span>
                        <span>Regular</span>
                        <span>Bom</span>
                        <span>Ótimo</span>
                        <span>Maravilhoso!</span>
                    </div>
                    <div class="stars" role="radiogroup" aria-label="Tempo das aulas">
                        <input type="radio" id="tempo-5" name="tempo" value="5" required>
                        <label for="tempo-5" title="Maravilhoso!" data-label="Maravilhoso!"></label>
                        <input type="radio" id="tempo-4" name="tempo" value="4">
                        <label for="tempo-4" title="Ótimo" data-label="Ótimo"></label>
                        <input type="radio" id="tempo-3" name="tempo" value="3">
                        <label for="tempo-3" title="Bom" data-label="Bom"></label>
                        <input type="radio" id="tempo-2" name="tempo" value="2">
                        <label for="tempo-2" title="Regular" data-label="Regular"></label>
                        <input type="radio" id="tempo-1" name="tempo" value="1">
                        <label for="tempo-1" title="Precisa Melhorar" data-label="Precisa Melhorar"></label>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="q">
        <h2>5. Sugestões para melhorar o curso: <span class="required-mark">*</span></h2>
        <textarea id="sugestao" name="sugestao" required></textarea>
    </section>

    <section class="q">
    <h2>6. É necessário maior aprofundamento em algum tema? <span class="required-mark">*</span></h2>
        <textarea id="tema" name="tema" required></textarea>
    </section>

    <section class="q">
        <h2>7. Relate uma experiência pessoal (opcional)</h2>
        <textarea id="experiencia_pessoal" name="experiencia_pessoal"></textarea>
    </section>

    <section class="q">
        <h2>8. Mensagem final: <span class="required-mark">*</span></h2>
        <textarea id="final" name="final" required></textarea>
    </section>

    <button type="submit">Enviar</button>
</form>

</body>
</html>
