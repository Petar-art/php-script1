<?php

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$ZipFile = 'zipFile.zip';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $collectedEmails = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($collectedEmails, FILTER_VALIDATE_EMAIL)) {
        echo "$collectedEmails is a valid email address";
        saveEmail($collectedEmails); // Call the function to save email to text file
        // No need to send the email immediately; you can schedule it using cron jobs
    } else {
        echo "$collectedEmails is an invalid email address";
    }
}

function saveEmail($collectedEmails)
{
    global $ZipFile;

    $zip = new ZipArchive();

    if ($zip->open($ZipFile, ZipArchive::CREATE) === TRUE) {
        $textFileName = 'email_addresses.txt';

        // Check if the file already exists in the ZIP archive
        if ($zip->locateName($textFileName) !== false) {
            // If it exists, read the existing content
            $existingContent = $zip->getFromName($textFileName);
        } else {
            // If it doesn't exist, initialize with an empty string
            $existingContent = '';
        }

        // Append the new email with a newline
        $newContent = $existingContent . $collectedEmails . "\n";

        // Update the file within the ZIP archive
        $zip->addFromString($textFileName, $newContent);
        $zip->close();
        echo "Email address added to zip file successfully.\n";
    } else {
        echo "Failed to create or open the zip file.\n";
    }
}

function sendAttachment()
{
    global $ZipFile;

    $smtpUsername = 'd9800b4c4de47c';
    $smtpPassword = '8e73d8508c68bd';

    $to = 'petarlevajac@gmail.com';
    $subject = 'Collected Emails';

    $filePath = $ZipFile;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('levkefejs@gmail.com', 'Your Name');
        $mail->addAddress($to);

        $mail->addAttachment($filePath);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = 'Attached is the collected emails file.';

        $mail->send();
        echo 'Email with attachment has been sent.';
        // Delete the file after sending
        unlink($filePath);
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}";
    }
}
?>
