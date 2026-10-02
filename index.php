<?php require 'config.php'; $title = 'Dashboard';
$doctors  = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$stmt = $pdo->prepare("SELECT a.*, d.name AS doctor, p.name AS patient FROM appointments a
  JOIN doctors d ON d.doctor_id=a.doctor_id JOIN patients p ON p.patient_id=a.patient_id
  WHERE a.appointment_date=CURDATE() ORDER BY a.appointment_time");
$stmt->execute(); $today = $stmt->fetchAll();
include 'includes/header.php'; ?>
<h1>Dashboard</h1>
<div class="stats">
  <a class="stat" href="doctors.php"><b><?= $doctors ?></b><span>Total doctors</span></a>
  <a class="stat" href="patients.php"><b><?= $patients ?></b><span>Total patients</span></a>
  <a class="stat hi" href="appointments.php?date=<?= date('Y-m-d') ?>"><b><?= count($today) ?></b><span>Appointments today</span></a>
</div>
<section class="card"><h2>Today's schedule (<?= date('d M Y') ?>)</h2>
<?php if (!$today): ?><p class="empty">No appointments today. <a href="appointments.php">Book one</a>.</p>
<?php else: ?><div class="scroll"><table><thead><tr><th>Time</th><th>Doctor</th><th>Patient</th><th>Reason</th><th>Status</th></tr></thead><tbody>
<?php foreach ($today as $a): ?><tr><td><?= e(substr($a['appointment_time'],0,5)) ?></td><td><?= e($a['doctor']) ?></td><td><?= e($a['patient']) ?></td><td><?= e($a['reason']) ?></td><td><span class="badge <?= strtolower(e($a['status'])) ?>"><?= e($a['status']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php include 'includes/footer.php'; ?>
