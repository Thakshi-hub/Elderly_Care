<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
requireLogin();

$user_id  = getCurrentUserId();
$username = getCurrentUsername();
$success  = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact'])) {
    $full_name    = sanitize($_POST['full_name'] ?? '');
    $relationship = sanitize($_POST['relationship'] ?? '');
    $phone1       = sanitize($_POST['phone1'] ?? '');
    $phone2       = sanitize($_POST['phone2'] ?? '');
    $has_poa      = isset($_POST['has_poa']) ? 1 : 0;

    if (!$full_name || !$relationship || !$phone1) {
        $error = 'Full name, relationship, and primary phone are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, full_name, relationship, phone1, phone2, has_poa) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $user_id, $full_name, $relationship, $phone1, $phone2, $has_poa);
        $stmt->execute() ? $success = 'Contact saved!' : $error = 'Failed to save.';
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM emergency_contacts WHERE id = $del_id AND user_id = $user_id");
    header("Location: contacts.php"); exit();
}

$contacts = [];
$res = $conn->query("SELECT * FROM emergency_contacts WHERE user_id = $user_id ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) $contacts[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Elderly Care – Contacts</title>
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
        <a href="contacts.php" class="topnav-link topnav-link--active">Emergency Contacts</a>
      </nav>
    </div>
    <div class="topbar-right">
      <a href="auth/logout.php" class="primary-btn small">Logout ⮞</a>
    </div>
  </header>

  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="brand-icon">EC</div>
      <span class="brand-text">CareDash</span>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sidebar-link">Dashboard</a>
      <a href="contacts.php" class="sidebar-link sidebar-link--active">Contacts</a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        <div class="user-meta">
          <span class="user-name"><?= htmlspecialchars($username) ?></span>
          <span class="user-role">Primary Caregiver</span>
        </div>
      </div>
    </div>
  </aside>

  <main class="content content--with-sidebar">
    <header class="page-header">
      <div>
        <p class="breadcrumb tiny muted">Home › Contacts</p>
        <h1>Contact &amp; Services</h1>
        <p class="muted">Manage emergency contacts for immediate care coordination.</p>
      </div>
      <a href="tel:911" class="primary-btn">Call 911</a>
    </header>

    <div class="layout">
      <div class="layout-main">
        <section class="card">
          <header class="card-header"><h2>Add New Emergency Contact</h2></header>
          <?php if ($success): ?><p style="color:#4ade80;font-size:0.85rem;margin-bottom:12px;"><?= $success ?></p><?php endif; ?>
          <?php if ($error): ?><p style="color:#f87171;font-size:0.85rem;margin-bottom:12px;"><?= $error ?></p><?php endif; ?>
          <form method="POST">
            <div class="grid grid--two">
              <label class="field small-field">
                <span class="field-label small">Full Name</span>
                <input type="text" name="full_name" placeholder="e.g. Kalai Nila" required />
              </label>
              <label class="field small-field">
                <span class="field-label small">Relationship</span>
                <input type="text" name="relationship" placeholder="e.g. Daughter" required />
              </label>
              <label class="field small-field">
                <span class="field-label small">Primary Phone</span>
                <input type="tel" name="phone1" placeholder="+94 777033120" required />
              </label>
              <label class="field small-field">
                <span class="field-label small">Secondary Phone</span>
                <input type="tel" name="phone2" placeholder="+94 775348720" />
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <strong>Power of Attorney</strong>
                <p class="muted tiny">Does this contact have legal health authority?</p>
              </div>
              <label class="switch">
                <input type="checkbox" name="has_poa" />
                <span class="slider"></span>
              </label>
            </div>
            <button type="submit" name="save_contact" class="primary-btn full small">Save Contact</button>
          </form>
        </section>

        <section class="card">
          <header class="card-header">
            <h2>Saved Contacts</h2>
            <span class="tiny muted"><?= count($contacts) ?> total</span>
          </header>
          <?php if (empty($contacts)): ?>
            <p class="muted tiny">No contacts saved yet.</p>
          <?php else: ?>
            <div class="grid grid--two">
              <?php foreach ($contacts as $c): ?>
                <article class="contact-card">
                  <div class="contact-pill">
                    <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                    <span class="tag"><?= htmlspecialchars($c['relationship']) ?></span>
                    <?php if ($c['has_poa']): ?><span class="tag dark">POA</span><?php endif; ?>
                  </div>
                  <p class="tiny muted"><?= htmlspecialchars($c['phone1']) ?></p>
                  <div class="contact-actions">
                    <a href="tel:<?= htmlspecialchars($c['phone1']) ?>" class="primary-btn tiny" style="text-decoration:none;">Call</a>
                    <a href="contacts.php?delete=<?= $c['id'] ?>" class="ghost-btn tiny"
                       onclick="return confirm('Delete this contact?')">Delete</a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>

      <aside class="layout-side">
        <section class="card">
          <header class="card-header"><h2>Local Emergency Services</h2></header>
          <ul class="service-list">
            <li><strong>City General Hospital</strong><p class="tiny muted">1.2 miles · Emergency (24/7)</p></li>
            <li><strong>Mother Care</strong><p class="tiny muted">2.0 miles away</p></li>
            <li><strong>Royal 24/7 Pharmacy</strong><p class="tiny muted">500m away</p></li>
          </ul>
        </section>
        <section class="card card--dark">
          <h2 class="card-title-light">Safety Checklist</h2>
          <ul class="safety-list">
            <li>Update contacts every 6 months</li>
            <li>Verify Power of Attorney docs are on file</li>
            <li>Print a physical copy for the fridge</li>
          </ul>
        </section>
      </aside>
    </div>
  </main>
  <script src="app.js"></script>
</body>
</html>