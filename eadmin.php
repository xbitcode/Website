<?php
$adminpass = "admin";

// Authentication
session_start();
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $adminpass) {
        $_SESSION['authenticated'] = true;
    } else {
        echo '<form method="post">';
        echo 'Password: <input type="password" name="password" />';
        echo '<button type="submit">Login</button>';
        echo '</form>';
        exit;
    }
}

// Mailgun configuration
$mailgunDomain = "secure-main.xbitcode.com";
$mailgunApiKey = "ee35f757bd69d8cefecd28bca2e693d8-79295dd0-6b2190e9";
$fromEmails = [
    "support@xbitcode.com",
    "billing@xbitcode.com"
];

// Function to send mail
function sendMail($from, $to, $subject, $message, $mailgunDomain, $mailgunApiKey) {
    $url = "https://api.mailgun.net/v3/$mailgunDomain/messages";
    $fields = [
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'html' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $mailgunApiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    $result = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpStatus === 200 ? "Mail sent successfully." : "Error sending mail: $result";
}

// Handle form submission
$status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $from = $_POST['from_email'] ?? $fromEmails[0];
    $to = $_POST['to_email'] ?? "";
    $subject = $_POST['subject'] ?? "No Subject";
    $message = $_POST['message'] ?? "";

    if (!empty($to) && !empty($message)) {
        $status = sendMail($from, $to, $subject, $message, $mailgunDomain, $mailgunApiKey);
    } else {
        $status = "Please fill out all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailgun Email Sender</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 600px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, textarea, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .status {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Mailgun Email Sender</h1>
    <form method="post">
        <label for="from_email">From Email</label>
        <select id="from_email" name="from_email">
            <?php foreach ($fromEmails as $email): ?>
                <option value="<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="to_email">To Email(s)</label>
        <input type="text" id="to_email" name="to_email" placeholder="recipient@example.com, another@example.com" />

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" placeholder="Subject of the email" />

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="10" placeholder="Write your email here... Supports HTML."></textarea>

        <button type="submit" name="send_email">Send Email</button>
    </form>

    <?php if ($status): ?>
        <div class="status">
            <?= htmlspecialchars($status) ?>
        </div>
    <?php endif; ?>
</body>
</html>
