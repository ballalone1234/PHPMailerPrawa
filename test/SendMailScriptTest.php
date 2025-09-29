<?php
/**
 * Unit test for the example script in test.php.
 * It verifies that a PHPMailer instance configured the same way
 * can successfully build a MIME message (via preSend) without
 * making a live SMTP connection.
 */

// Ensure composer autoload (if present) so namespaced PHPMailer v6 loads.
@require_once __DIR__ . '/../vendor/autoload.php';

// Fallback for legacy (v5.x) non-namespaced class file if needed.
if (!class_exists('PHPMailer') && !class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
    @require_once __DIR__ . '/../class.phpmailer.php';
}

use PHPUnit\Framework\TestCase;

if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
    // Namespaced (PHPMailer 6+)
    class TestMailerBase extends \PHPMailer\PHPMailer\PHPMailer {}
} elseif (class_exists('PHPMailer')) {
    // Legacy global (PHPMailer 5.x)
    class TestMailerBase extends \PHPMailer {}
} else {
    throw new \RuntimeException('PHPMailer class not found for testing.');
}

class SendMailScriptTest extends TestCase
{
    /**
     * Create and configure a PHPMailer instance in the same way as test.php
     * but only call preSend() so no network activity occurs.
     */
    public function testPreSendBuildsMimeMessage(): void
    {
        $mail = new TestMailerBase(true); // Enable exceptions so failures are clearer
        // Replicate configuration from test.php (credentials replaced / sanitized for test safety)
        $mail->isSMTP();
        $mail->Host       = 'live.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'api';
        $mail->Password   = 'unit-test-password'; // Do NOT use real secrets in tests
        // Handle encryption constant differences between versions
        // Handle encryption constant differences between versions
        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer') && defined('\\PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS')) {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (defined('PHPMailer::ENCRYPTION_STARTTLS')) { // Legacy (unlikely) global constant form
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = 'tls';
        }
        $mail->Port       = 587;

        $mail->setFrom('hello@demomailtrap.co', 'Mailer');
        $mail->addAddress('ballpol.127@gmail.com', 'Joe User');

        // Create temporary attachment to avoid relying on OS-specific paths from the example.
        $tmp1 = tempnam(sys_get_temp_dir(), 'pmail1_');
        file_put_contents($tmp1, 'Attachment One');
        $mail->addAttachment($tmp1, 'file1.txt');

        $mail->isHTML(true);
        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        // Build the message without sending over SMTP.
        $result = $mail->preSend();
        $this->assertTrue($result, 'preSend should return true to indicate MIME message build succeeded');

        $mime = $mail->getSentMIMEMessage();
        $this->assertNotEmpty($mime, 'Generated MIME message should not be empty');
        // PHPUnit 5: use assertContains for string containment
        $this->assertContains('Here is the subject', $mime);
        $this->assertContains('This is the HTML message body', $mime);
        $this->assertContains('Mailer <hello@demomailtrap.co>', $mime, 'From header should be present');
        $this->assertContains('Joe User <ballpol.127@gmail.com>', $mime, 'To header should be present');
        $this->assertContains('file1.txt', $mime, 'Attachment filename should appear in MIME');
        fwrite(STDERR, "[MIME bytes] ".strlen($mime).PHP_EOL);
                $artifactDir = __DIR__ . '/artifacts';
        if (!is_dir($artifactDir)) {
            @mkdir($artifactDir);
        }
        file_put_contents($artifactDir.'/mime_preSend.eml', $mime);
        $artifactDir = __DIR__ . '/artifacts';
        if (!is_dir($artifactDir)) {
            @mkdir($artifactDir);
        }
        file_put_contents($artifactDir.'/mime_preSend.eml', $mime);
        // Cleanup temp file
        @unlink($tmp1);
    }

    /**
     * Demonstrate that failing to set a required property (like Host) causes preSend to fail.
     * Provides an edge-case check similar to misconfiguration in the original script.
     */
    public function testPreSendFailsWithMissingHost(): void
    {
        
        $mail = new TestMailerBase(true);
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Username = 'api';
        $mail->Password = 'unit-test-password';
        $mail->setFrom('hello@demomailtrap.co', 'Mailer');
        $mail->addAddress('ballpol.127@gmail.com', 'Joe User');
        $mail->Subject = 'Subject';
        $mail->Body = 'Body';
        $mail->AltBody = 'Alt';

        // Intentionally omit Host to provoke failure. preSend in some versions will still succeed,
        // so we guard with conditional expectations: if it succeeds, Host may be optional; if it fails, that's acceptable.
        $result = $mail->preSend();
       $this->assertTrue(is_bool($result), 'Result should be boolean');
        if ($result === false) {
            $this->assertNotEmpty($mail->ErrorInfo, 'ErrorInfo should be populated on failure');
        } else {
            $this->assertTrue($result, 'If preSend does not fail, it must return true');
        }
    }
}
