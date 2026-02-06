<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/Exception.php';
require 'lib/PHPMailer.php';
require 'lib/SMTP.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // TRAVA ANTI-BOT (Honeypot)
    if (!empty($_POST['sobrenome_valida'])) {
        exit; // Se o campo invisível for preenchido, o script para aqui.
    }

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURAÇÃO DO SERVIDOR ---
        // Se não houver .env configurado, ele usará os valores abaixo
        // Recomenda-se mover para variáveis de ambiente antes do Git Push
        $smtp_user = 'Taveira067@gmail.com'; 
        $smtp_pass = 'vegykuskvlgrkzrp'; 

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user; 
        $mail->Password   = $smtp_pass; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setLanguage('pt_br');

        // --- DESTINATÁRIOS ---
        $mail->setFrom($smtp_user, 'Sistema ERPCON');
        $mail->addAddress($smtp_user); 

        // Sanitização e Captura de Dados
        $email_cliente = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $motivo = htmlspecialchars($_POST['motivo'] ?? 'Newsletter/Contato', ENT_QUOTES, 'UTF-8');

        if (filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($email_cliente);
        }

        // --- CONTEÚDO ---
        $mail->isHTML(true);
        $mail->Subject = 'Novo Lead: ' . $motivo;
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2 style='color: #2563eb;'>Novo contato recebido via site!</h2>
                <p><b>E-mail do interessado:</b> {$email_cliente}</p>
                <p><b>Origem/Assunto:</b> {$motivo}</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>Este é um e-mail automático gerado pelo seu site.</p>
            </div>
        ";

        $mail->send();
        echo json_encode(["tipo" => "success", "mensagem" => "Inscrição realizada com sucesso!"]);

    } catch (Exception $e) {
        echo json_encode(["tipo" => "error", "mensagem" => "Não foi possível processar agora."]);
    }
} else {
    echo json_encode(["tipo" => "error", "mensagem" => "Acesso inválido."]);
}