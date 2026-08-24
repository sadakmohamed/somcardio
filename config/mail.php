<?php
/**
 * Minimal authenticated SMTP sender for the contact form.
 */

function sendSmtpMail(string $to, string $subject, string $htmlBody, string $replyTo): void {
    $host = (string) env('SMTP_HOST', 'smtp.gmail.com');
    $port = (int) env('SMTP_PORT', 587);
    $username = trim((string) env('SMTP_USER', ''));
    $password = (string) env('SMTP_PASS', '');
    $encryption = strtolower((string) env('SMTP_ENCRYPTION', 'tls'));

    if ($username === '' || $password === '') {
        throw new RuntimeException('SMTP_USER and SMTP_PASS are not configured.');
    }

    $scheme = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $socket = stream_socket_client($scheme . $host . ':' . $port, $errorCode, $errorMessage, 15);
    if (!$socket) {
        throw new RuntimeException('Could not connect to the SMTP server.');
    }

    stream_set_timeout($socket, 15);
    try {
        smtpExpect($socket, 220);
        smtpCommand($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

        if ($encryption === 'tls') {
            smtpCommand($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Could not establish SMTP encryption.');
            }
            smtpCommand($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
        }

        smtpCommand($socket, 'AUTH LOGIN', 334);
        smtpCommand($socket, base64_encode($username), 334);
        smtpCommand($socket, base64_encode($password), 235);
        smtpCommand($socket, 'MAIL FROM:<' . $username . '>', 250);
        smtpCommand($socket, 'RCPT TO:<' . $to . '>', 250);
        smtpCommand($socket, 'DATA', 354);

        $headers = [
            'From: ' . $username,
            'Reply-To: ' . str_replace(["\r", "\n"], '', $replyTo),
            'To: ' . $to,
            'Subject: ' . str_replace(["\r", "\n"], '', $subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];
        $body = preg_replace('/^(\.)/m', '.$1', $htmlBody);
        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        smtpExpect($socket, 250);
        smtpCommand($socket, 'QUIT', 221);
    } finally {
        fclose($socket);
    }
}

function smtpCommand($socket, string $command, int $expectedCode): void {
    fwrite($socket, $command . "\r\n");
    smtpExpect($socket, $expectedCode);
}

function smtpExpect($socket, int $expectedCode): void {
    $response = '';
    do {
        $line = fgets($socket);
        if ($line === false) {
            throw new RuntimeException('The SMTP server closed the connection.');
        }
        $response .= $line;
    } while (isset($line[3]) && $line[3] === '-');

    if ((int) substr($response, 0, 3) !== $expectedCode) {
        throw new RuntimeException('SMTP server rejected the request.');
    }
}