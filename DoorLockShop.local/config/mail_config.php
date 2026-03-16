<?php
/**
 * Конфигурация отправки писем через Gmail SMTP.
 * По примеру лабораторной работы (lr25.local).
 * Не публикуйте этот файл с реальными данными в открытом доступе.
 *
 * Настройка: укажите GMAIL_USER и GMAIL_APP_PASSWORD.
 * Пароль приложения: Google → Безопасность → Пароли приложений.
 */

// Адрес администратора, куда приходят заявки с сайта
$ADMIN_EMAIL = 'nikitahartanovic010@gmail.com';

// Учётные данные Gmail
const GMAIL_USER         = 'nikitahartanovic010@gmail.com';
const GMAIL_APP_PASSWORD = 'cqidyjsxsofqapuc';

/**
 * Отправка HTML-письма через SMTP Gmail (ssl://smtp.gmail.com:465, AUTH LOGIN).
 * @param string $to       Адрес получателя
 * @param string $subject  Тема письма (UTF-8)
 * @param string $htmlBody Тело письма в HTML
 * @return bool
 */
function site_send_mail(string $to, string $subject, string $htmlBody): bool
{
    $host    = 'ssl://smtp.gmail.com';
    $port    = 465;
    $timeout = 30;
    $errno   = 0;
    $errstr  = '';

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp) {
        error_log("Gmail SMTP connect failed: {$errno} {$errstr}");
        return false;
    }

    stream_set_timeout($fp, $timeout);

    $read = function () use ($fp): string {
        $data = '';
        while ($str = fgets($fp, 515)) {
            $data .= $str;
            if (isset($str[3]) && $str[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $write = function (string $cmd) use ($fp): void {
        fwrite($fp, $cmd . "\r\n");
    };

    // Приветствие сервера
    $resp = $read();
    if (strpos($resp, '220') !== 0) {
        fclose($fp);
        return false;
    }

    // EHLO
    $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $write("EHLO {$serverName}");
    $resp = $read();
    if (strpos($resp, '250') !== 0) {
        fclose($fp);
        return false;
    }

    // AUTH LOGIN
    $write('AUTH LOGIN');
    $resp = $read();
    if (strpos($resp, '334') !== 0) {
        fclose($fp);
        return false;
    }

    $write(base64_encode(GMAIL_USER));
    $resp = $read();
    if (strpos($resp, '334') !== 0) {
        fclose($fp);
        return false;
    }

    $write(base64_encode(GMAIL_APP_PASSWORD));
    $resp = $read();
    if (strpos($resp, '235') !== 0) {
        fclose($fp);
        return false;
    }

    // MAIL FROM / RCPT TO / DATA
    $fromEmail = GMAIL_USER;
    $fromName  = 'GoldenService';

    $write("MAIL FROM:<{$fromEmail}>");
    $resp = $read();
    if (strpos($resp, '250') !== 0) {
        fclose($fp);
        return false;
    }

    $write("RCPT TO:<{$to}>");
    $resp = $read();
    if (strpos($resp, '250') !== 0 && strpos($resp, '251') !== 0) {
        fclose($fp);
        return false;
    }

    $write('DATA');
    $resp = $read();
    if (strpos($resp, '354') !== 0) {
        fclose($fp);
        return false;
    }

    // Заголовки и тело письма
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers  = 'From: ' . sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($fromName), $fromEmail) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    $message  = $headers;
    $message .= "To: <{$to}>\r\n";
    $message .= "Subject: {$encodedSubject}\r\n";
    $message .= "\r\n";
    $message .= $htmlBody . "\r\n";
    $message .= ".\r\n";

    fwrite($fp, $message);
    $resp = $read();
    if (strpos($resp, '250') !== 0) {
        fclose($fp);
        return false;
    }

    $write('QUIT');
    fclose($fp);

    return true;
}
