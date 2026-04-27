<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class Mailer
{
    private $mail;

    public function __construct()
    {
        // Ensure .env is loaded if not already
        if (!isset($_SERVER['SMTP_HOST']) && !isset($_ENV['SMTP_HOST'])) {
            try {
                $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
                $dotenv->load();
            } catch (\Exception $e) {
                // Silently fail if .env not found
            }
        }

        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = $_SERVER['SMTP_HOST'] ?? $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_SERVER['SMTP_USER'] ?? $_ENV['SMTP_USER'] ?? '';
        $this->mail->Password   = $_SERVER['SMTP_PASS'] ?? $_ENV['SMTP_PASS'] ?? '';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $_SERVER['SMTP_PORT'] ?? $_ENV['SMTP_PORT'] ?? 587;

        // Default From
        $fromEmail = $_SERVER['SMTP_FROM'] ?? $_ENV['SMTP_FROM'] ?? '';
        $fromName  = $_SERVER['SMTP_FROM_NAME'] ?? $_ENV['SMTP_FROM_NAME'] ?? 'SmartQ Admin';
        $this->mail->setFrom($fromEmail, $fromName);
    }

    public function sendEmail($to, $subject, $body, $altBody = '')
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody ?: strip_tags($body);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }
}
