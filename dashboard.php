<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
requireLogin();

$user_id  = getCurrentUserId();
$username = getCurrentUsername();

// Add medication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medication'])) {
    $name     = sanitize($_POST['med_name'] ?? '');
    $dosage   = sanitize($_POST['med_dose'] ?? '');
    $schedule = sanitize($_POST['med_schedule'] ?? 'Custom schedule');
    if ($name && $dosage) {
        $stmt = $conn->prepare("INSERT INTO medications (user_id, name, dosage, schedule) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $name, $dosage, $schedule);
        $stmt->execute(); $stmt->close();
    }
    header("Location: dashboard.php#medication"); exit();
}

// Mark medication taken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_taken'])) {
    $med_id = (int)$_POST['med_id'];
    $conn->query("UPDATE medications SET is_taken = 1 WHERE id = $med_id AND user_id = $user_id");
    header("Location: dashboard.php#medication"); exit();
}

// Add task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task = sanitize($_POST['task_name'] ?? '');
    if ($task) {
        $stmt = $conn->prepare("INSERT INTO checklist_items (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        $stmt->execute(); $stmt->close();
    }
    header("Location: dashboard.php#checklist"); exit();
}

// Toggle task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_task'])) {
    $task_id   = (int)$_POST['task_id'];
    $completed = (int)$_POST['is_completed'];
    $conn->query("UPDATE checklist_items SET is_completed = $completed WHERE id = $task_id AND user_id = $user_id");
    header("Location: dashboard.php#checklist"); exit();
}

// Add appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $doctor   = sanitize($_POST['doctor'] ?? '');
    $datetime = sanitize($_POST['appt_datetime'] ?? '');
    $location = sanitize($_POST['appt_location'] ?? '');
    $notes    = sanitize($_POST['appt_notes'] ?? '');
    if ($doctor && $datetime) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor, appointment_date, location, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $doctor, $datetime, $location, $notes);
        $stmt->execute(); $stmt->close();
    }
    header("Location: dashboard.php#appointments"); exit();
}

// Fetch data
$meds = [];
$res = $conn->query("SELECT * FROM medications WHERE user_id = $user_id ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) $meds[] = $row;

$tasks = [];
$res = $conn->query("SELECT * FROM checklist_items WHERE user_id = $user_id ORDER BY created_at ASC");
while ($row = $res->fetch_assoc()) $tasks[] = $row;

$appts = [];
$res = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date ASC");
while ($row = $res->fetch_assoc()) $appts[] = $row;

$contacts = [];
$res = $conn->query("SELECT * FROM emergency_contacts WHERE user_id = $user_id ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) $contacts[] = $row;

$total_tasks     = count($tasks);
$completed_tasks = count(array_filter($tasks, fn($t) => $t['is_completed']));
$pending_meds    = count(array_filter($meds, fn($m) => !$m['is_taken']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Elderly Care – Dashboard</title>
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
        <a href="dashboard.php" class="topnav-link topnav-link--active">Dashboard</a>
        <a href="contacts.php" class="topnav-link">Emergency Contacts</a>
      </nav>
    </div>
    <div class="topbar-right">
      <span class="muted small" style="margin-right:8px;">Hello, <?= htmlspecialchars($username) ?></span>
      <a href="auth/logout.php" class="primary-btn small">Logout ⮞</a>
    </div>
  </header>

  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="brand-icon">EC</div>
      <span class="brand-text">CareDash</span>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sidebar-link sidebar-link--active">Dashboard</a>
      <button class="sidebar-link sidebar-link--button activity-tab activity-tab--active" data-target="checklist">Checklist</button>
      <button class="sidebar-link sidebar-link--button activity-tab" data-target="medication">Medication</button>
      <button class="sidebar-link sidebar-link--button activity-tab" data-target="appointments">Appointments</button>
      <button class="sidebar-link sidebar-link--button activity-tab" data-target="contacts">Contacts</button>
      <a href="contacts.php" class="sidebar-link">Emergency Contacts</a>
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
        <h1>Daily Care Overview</h1>
        <p class="muted">Manage medications, checklist, appointments, and contacts.</p>
      </div>
      <div class="page-header-actions">
        <a href="contact.php" class="ghost-btn small">Contact Support</a>
        <a href="auth/logout.php" class="primary-btn small">Logout ⮞</a>
      </div>
    </header>

    <section class="card activity-switcher-card">
      <header class="card-header">
        <h2>Activities</h2>
        <span class="badge" id="currentActivityBadge">Checklist</span>
      </header>
      <div class="activity-switcher">
        <button class="ghost-btn small activity-chip activity-tab activity-tab--active" data-target="checklist">Daily Checklist</button>
        <button class="ghost-btn small activity-chip activity-tab" data-target="medication">Medication</button>
        <button class="ghost-btn small activity-chip activity-tab" data-target="appointments">Appointments</button>
        <button class="ghost-btn small activity-chip activity-tab" data-target="contacts">Emergency Contacts</button>
      </div>
    </section>

    <div class="layout">
      <div class="layout-main">

        <!-- CHECKLIST -->
        <section class="card activity-panel activity-panel--active" id="panel-checklist">
          <header class="card-header">
            <h2>Daily Checklist</h2>
            <span class="badge checklist-badge">
              <?= $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0 ?>% Completed
            </span>
          </header>
          <div class="progress-bar">
            <div class="progress-fill checklist-progress"
                 style="width:<?= $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0 ?>%"></div>
          </div>
          <form method="POST" style="display:flex;gap:10px;margin-bottom:14px;">
            <input type="text" name="task_name" placeholder="Add new task..." required
                   style="flex:1;border:1px solid rgba(255,255,255,0.2);border-radius:10px;padding:8px 12px;background:rgba(255,255,255,0.08);color:#fff;font-family:inherit;" />
            <button type="submit" name="add_task" class="primary-btn small">+ Add</button>
          </form>
          <div class="grid grid--two checklist-grid">
            <?php foreach ($tasks as $task): ?>
              <form method="POST">
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>" />
                <input type="hidden" name="is_completed" value="<?= $task['is_completed'] ? 0 : 1 ?>" />
                <label class="check-card">
                  <input type="checkbox" class="checklist-item"
                         <?= $task['is_completed'] ? 'checked' : '' ?>
                         onchange="this.form.submit()" name="toggle_task" />
                  <span><?= htmlspecialchars($task['task']) ?></span>
                </label>
              </form>
            <?php endforeach; ?>
            <?php if (empty($tasks)): ?>
              <p class="muted tiny" style="grid-column:span 2;">No tasks yet. Add one above!</p>
            <?php endif; ?>
          </div>
        </section>

        <!-- MEDICATION -->
        <section class="card activity-panel" id="panel-medication">
          <header class="card-header"><h2>Medication Reminders</h2></header>
          <div class="med-new">
            <h3 class="small-heading">Add New Medication</h3>
            <form method="POST">
              <div class="grid grid--three">
                <label class="field small-field">
                  <span class="field-label small">Medication Name</span>
                  <input type="text" name="med_name" placeholder="e.g. Lisinopril" required />
                </label>
                <label class="field small-field">
                  <span class="field-label small">Dosage</span>
                  <input type="text" name="med_dose" placeholder="e.g. 10mg" required />
                </label>
                <button type="submit" name="add_medication" class="primary-btn small full" style="margin-top:20px;">+ Add</button>
              </div>
            </form>
          </div>
          <ul class="med-list">
            <?php foreach ($meds as $med): ?>
              <li class="med-item <?= $med['is_taken'] ? 'med-item--success' : '' ?>">
                <div>
                  <strong><?= htmlspecialchars($med['name']) ?></strong>
                  <p class="muted tiny"><?= htmlspecialchars($med['dosage']) ?> · <?= htmlspecialchars($med['schedule']) ?></p>
                </div>
                <?php if ($med['is_taken']): ?>
                  <span class="status-pill success">Completed</span>
                <?php else: ?>
                  <form method="POST">
                    <input type="hidden" name="med_id" value="<?= $med['id'] ?>" />
                    <button type="submit" name="mark_taken" class="ghost-btn tiny">Mark Taken</button>
                  </form>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
            <?php if (empty($meds)): ?>
              <li style="list-style:none;"><p class="muted tiny">No medications added yet.</p></li>
            <?php endif; ?>
          </ul>
        </section>

        <!-- APPOINTMENTS -->
        <section class="card activity-panel" id="panel-appointments">
          <header class="card-header"><h2>Appointments</h2></header>
          <div class="med-new" style="margin-bottom:14px;">
            <h3 class="small-heading">Schedule New Appointment</h3>
            <form method="POST">
              <div class="grid grid--two">
                <label class="field small-field">
                  <span class="field-label small">Doctor / Specialist</span>
                  <input type="text" name="doctor" placeholder="e.g. Dr. Sarah" required />
                </label>
                <label class="field small-field">
                  <span class="field-label small">Date & Time</span>
                  <input type="datetime-local" name="appt_datetime" required />
                </label>
                <label class="field small-field">
                  <span class="field-label small">Location</span>
                  <input type="text" name="appt_location" placeholder="e.g. St. Mary's Hospital" />
                </label>
                <label class="field small-field">
                  <span class="field-label small">Notes</span>
                  <input type="text" name="appt_notes" placeholder="e.g. Bring test reports" />
                </label>
              </div>
              <button type="submit" name="add_appointment" class="primary-btn small">Save Appointment</button>
            </form>
          </div>
          <ul class="appt-list">
            <?php foreach ($appts as $appt): ?>
              <li>
                <span class="appt-date tiny muted"><?= date('M d · g:i A', strtotime($appt['appointment_date'])) ?></span>
                <p><strong><?= htmlspecialchars($appt['doctor']) ?></strong></p>
                <p class="muted tiny"><?= htmlspecialchars($appt['location']) ?>
                  <?= $appt['notes'] ? ' · ' . htmlspecialchars($appt['notes']) : '' ?></p>
              </li>
            <?php endforeach; ?>
            <?php if (empty($appts)): ?>
              <li style="list-style:none;"><p class="muted tiny">No appointments yet.</p></li>
            <?php endif; ?>
          </ul>
        </section>

        <!-- CONTACTS -->
        <section class="card activity-panel" id="panel-contacts">
          <header class="card-header">
            <h2>Emergency Contacts</h2>
            <a href="contacts.php" class="link-btn tiny">Manage All</a>
          </header>
          <div class="emergency-grid">
            <?php foreach (array_slice($contacts, 0, 4) as $c): ?>
              <div class="emergency-contact emergency-contact--light">
                <p class="tiny muted"><?= htmlspecialchars($c['relationship']) ?></p>
                <p class="contact-name"><?= htmlspecialchars($c['full_name']) ?></p>
                <p class="tiny muted"><?= htmlspecialchars($c['phone1']) ?></p>
                <a href="tel:<?= htmlspecialchars($c['phone1']) ?>" class="primary-btn full small" style="text-align:center;text-decoration:none;display:block;">Call</a>
              </div>
            <?php endforeach; ?>
            <?php if (empty($contacts)): ?>
              <p class="muted tiny">No contacts saved. <a href="contacts.php" class="link-btn small">Add one</a>.</p>
            <?php endif; ?>
          </div>
        </section>

      </div>

      <aside class="layout-side">
        <section class="card">
          <header class="card-header"><h2>Today Summary</h2></header>
          <ul class="service-list">
            <li><strong>Checklist</strong><p class="tiny muted"><?= $completed_tasks ?> of <?= $total_tasks ?> tasks completed</p></li>
            <li><strong>Medication</strong><p class="tiny muted"><?= $pending_meds ?> reminder(s) pending</p></li>
            <li><strong>Appointments</strong><p class="tiny muted"><?= count($appts) ?> upcoming</p></li>
            <li><strong>Contacts</strong><p class="tiny muted"><?= count($contacts) ?> saved</p></li>
          </ul>
        </section>
        <section class="card card--dark side-panel side-panel--active" id="side-checklist">
          <h2 class="card-title-light">Checklist Notes</h2>
          <p class="tiny muted-light">Keep your daily health tasks updated.</p>
        </section>
        <section class="card side-panel" id="side-medication">
          <h2>Medication Status</h2>
          <p class="tiny muted"><?= $pending_meds ?> medication(s) still pending today.</p>
        </section>
        <section class="card side-panel" id="side-appointments">
          <h2>Upcoming Visit</h2>
          <p class="tiny muted"><?= !empty($appts) ? 'Next: ' . htmlspecialchars($appts[0]['doctor']) . ' on ' . date('M d', strtotime($appts[0]['appointment_date'])) : 'No upcoming appointments.' ?></p>
        </section>
        <section class="card side-panel" id="side-contacts">
          <h2>Emergency Info</h2>
          <p class="tiny muted">Call your primary doctor first in any emergency.</p>
        </section>
      </aside>
    </div>
  </main>
  <script src="app.js"></script>
</body>
</html>