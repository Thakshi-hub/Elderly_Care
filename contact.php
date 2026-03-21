<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        $error = 'All fields are required.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute() ? $success = 'Message sent successfully!' : $error = 'Failed. Please try again.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Elderly Care – Contact</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header class="topbar">
    <div class="topbar-left">
      <div class="brand">
        <div class="brand-icon">EC</div>
        <span class="brand-text">Elderly Care</span>
      </div>
      <nav class="topnav">
        <a href="index.php" class="topnav-link">Home</a>
        <a href="dashboard.php" class="topnav-link">Dashboard</a>
        <a href="contacts.php" class="topnav-link">Emergency Contacts</a>
      </nav>
    </div>
    <div class="topbar-right">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="auth/logout.php" class="primary-btn small">Logout ⮞</a>
      <?php else: ?>
        <a href="auth/login.php" class="primary-btn small">Login ⮞</a>
      <?php endif; ?>
    </div>
  </header>

  <main class="content" style="max-width:600px;">
    <header class="page-header">
      <div>
        <h1>Contact Support</h1>
        <p class="muted">Have a question? Send us a message.</p>
      </div>
    </header>
    <section class="card">
      <?php if ($success): ?><p style="color:#4ade80;margin-bottom:16px;"><?= $success ?></p><?php endif; ?>
      <?php if ($error): ?><p style="color:#f87171;margin-bottom:16px;"><?= $error ?></p><?php endif; ?>
      <form method="POST">
        <label class="field">
          <span class="field-label">Your Name</span>
          <input type="text" name="name" placeholder="e.g. Maathu" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
        </label>
        <label class="field">
          <span class="field-label">Email Address</span>
          <input type="email" name="email" placeholder="e.g. name@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
        </label>
        <label class="field">
          <span class="field-label">Message</span>
          <textarea name="message" rows="5" required placeholder="Write your message here..."
            style="width:100%;border:1px solid rgba(255,255,255,0.2);border-radius:10px;padding:10px 12px;background:rgba(255,255,255,0.08);color:#fff;font-family:inherit;resize:vertical;"
          ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </label>
        <button type="submit" class="primary-btn full">Send Message ⮞</button>
      </form>
    </section>
  </main>
</body>
</html>