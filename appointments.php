<?php require 'config.php'; $title = 'Appointments';
const STATUSES = ['Booked', 'Completed', 'Cancelled'];
$a = ['appointment_id'=>'','doctor_id'=>'','patient_id'=>'','appointment_date'=>'','appointment_time'=>'','status'=>'Booked','reason'=>'']; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'status') {
    $st = $_POST['status'] ?? '';
    if (in_array($st, STATUSES, true)) { $pdo->prepare("UPDATE appointments SET status=? WHERE appointment_id=?")->execute([$st, (int)$_POST['id']]); flash("Appointment marked $st."); }
    redirect('appointments.php?' . http_build_query(array_filter(['doctor_id'=>$_POST['f_doctor']??'','patient_id'=>$_POST['f_patient']??'','date'=>$_POST['f_date']??''])));
  }
  $a = array_map('trim', array_intersect_key($_POST, $a)) + $a;
  $isEdit = $a['appointment_id'] !== '';
  $x = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE doctor_id=?"); $x->execute([(int)$a['doctor_id']]); if (!$x->fetchColumn()) $errors[] = 'Select a doctor.';
  $x = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id=?"); $x->execute([(int)$a['patient_id']]); if (!$x->fetchColumn()) $errors[] = 'Select a patient.';
  $dt = DateTime::createFromFormat('Y-m-d', $a['appointment_date']);
  if (!$dt || $dt->format('Y-m-d') !== $a['appointment_date']) $errors[] = 'Choose a valid date.';
  elseif (!$isEdit && $a['appointment_date'] < date('Y-m-d')) $errors[] = 'New appointments cannot be in the past.';
  if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $a['appointment_time'])) $errors[] = 'Choose a valid time.';
  if (!in_array($a['status'], STATUSES, true)) $a['status'] = 'Booked';
  if (strlen($a['reason']) > 255) $errors[] = 'Reason must be 255 characters or fewer.';
  if (!$errors && $a['status'] !== 'Cancelled') { // prevent double booking
    $x = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND status<>'Cancelled' AND appointment_id<>?");
    $x->execute([$a['doctor_id'], $a['appointment_date'], $a['appointment_time'], (int)$a['appointment_id']]);
    if ($x->fetchColumn()) $errors[] = 'That doctor already has an appointment at this date and time.';
  }
  if (!$errors) {
    $v = [$a['doctor_id'], $a['patient_id'], $a['appointment_date'], $a['appointment_time'], $a['status'], $a['reason']];
    if ($isEdit) { $pdo->prepare("UPDATE appointments SET doctor_id=?,patient_id=?,appointment_date=?,appointment_time=?,status=?,reason=? WHERE appointment_id=?")->execute([...$v, $a['appointment_id']]); flash('Appointment updated.'); }
    else { $pdo->prepare("INSERT INTO appointments (doctor_id,patient_id,appointment_date,appointment_time,status,reason) VALUES (?,?,?,?,?,?)")->execute($v); flash('Appointment booked.'); }
    redirect('appointments.php');
  }
} elseif (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id=?"); $s->execute([(int)$_GET['edit']]);
  if ($r = $s->fetch()) { $a = $r; $a['appointment_time'] = substr($r['appointment_time'], 0, 5); }
}
$doctors = $pdo->query("SELECT doctor_id,name,specialization FROM doctors ORDER BY name")->fetchAll();
$patients = $pdo->query("SELECT patient_id,name FROM patients ORDER BY name")->fetchAll();
$fd = $_GET['doctor_id'] ?? ''; $fp = $_GET['patient_id'] ?? ''; $fdate = $_GET['date'] ?? '';
$w = []; $pa = [];
if ($fd !== '') { $w[] = 'a.doctor_id=?'; $pa[] = (int)$fd; }
if ($fp !== '') { $w[] = 'a.patient_id=?'; $pa[] = (int)$fp; }
if ($fdate !== '') { $w[] = 'a.appointment_date=?'; $pa[] = $fdate; }
$s = $pdo->prepare("SELECT a.*, d.name AS doctor, d.specialization, p.name AS patient FROM appointments a
  JOIN doctors d ON d.doctor_id=a.doctor_id JOIN patients p ON p.patient_id=a.patient_id"
  . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . " ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$s->execute($pa); $rows = $s->fetchAll();
include 'includes/header.php'; ?>
<h1>Appointments</h1>
<div class="grid">
<section class="card"><h2><?= $a['appointment_id'] ? 'Update appointment' : 'Book appointment' ?></h2>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<?php if (!$doctors || !$patients): ?><p class="empty">Add at least one <a href="doctors.php">doctor</a> and one <a href="patients.php">patient</a> first.</p><?php else: ?>
<form method="post" novalidate data-validate><input type="hidden" name="appointment_id" value="<?= e($a['appointment_id']) ?>">
  <label>Doctor<select name="doctor_id" required><option value="">Select doctor</option><?php foreach ($doctors as $d): ?><option value="<?= $d['doctor_id'] ?>" <?= $a['doctor_id']==$d['doctor_id']?'selected':'' ?>><?= e($d['name']) ?> (<?= e($d['specialization']) ?>)</option><?php endforeach; ?></select></label>
  <label>Patient<select name="patient_id" required><option value="">Select patient</option><?php foreach ($patients as $p): ?><option value="<?= $p['patient_id'] ?>" <?= $a['patient_id']==$p['patient_id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></label>
  <div class="two"><label>Date<input type="date" name="appointment_date" required <?= $a['appointment_id'] ? '' : 'min="'.date('Y-m-d').'"' ?> value="<?= e($a['appointment_date']) ?>"></label>
  <label>Time<input type="time" name="appointment_time" required value="<?= e($a['appointment_time']) ?>"></label></div>
  <?php if ($a['appointment_id']): ?><label>Status<select name="status"><?php foreach (STATUSES as $st): ?><option <?= $a['status']===$st?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select></label><?php endif; ?>
  <label>Reason<input name="reason" maxlength="255" value="<?= e($a['reason']) ?>" placeholder="e.g. Follow-up checkup"></label>
  <div class="row"><button class="btn"><?= $a['appointment_id'] ? 'Save changes' : 'Book appointment' ?></button>
  <?php if ($a['appointment_id']): ?><a class="btn ghost" href="appointments.php">Cancel</a><?php endif; ?></div>
</form><?php endif; ?></section>
<section class="card"><h2>All appointments (<?= count($rows) ?>)</h2>
<form class="filters" method="get">
  <select name="doctor_id"><option value="">All doctors</option><?php foreach ($doctors as $d): ?><option value="<?= $d['doctor_id'] ?>" <?= $fd==$d['doctor_id']&&$fd!==''?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select>
  <select name="patient_id"><option value="">All patients</option><?php foreach ($patients as $p): ?><option value="<?= $p['patient_id'] ?>" <?= $fp==$p['patient_id']&&$fp!==''?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select>
  <input type="date" name="date" value="<?= e($fdate) ?>">
  <button class="btn">Filter</button><?php if ($w): ?><a class="btn ghost" href="appointments.php">Clear</a><?php endif; ?>
</form>
<?php if (!$rows): ?><p class="empty">No appointments match.</p><?php else: ?><div class="scroll"><table><thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['appointment_date']) ?></td><td><?= e(substr($r['appointment_time'],0,5)) ?></td><td><?= e($r['doctor']) ?><small><?= e($r['specialization']) ?></small></td><td><?= e($r['patient']) ?></td><td><?= e($r['reason']) ?></td>
<td><span class="badge <?= strtolower(e($r['status'])) ?>"><?= e($r['status']) ?></span></td>
<td class="act"><a class="btn sm ghost" href="?edit=<?= $r['appointment_id'] ?>">Edit</a>
<?php if ($r['status'] === 'Booked'): foreach (['Completed'=>'ok','Cancelled'=>'danger'] as $st=>$cl): ?>
<form method="post" <?= $st==='Cancelled' ? 'data-confirm="Cancel this appointment?"' : '' ?>><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $r['appointment_id'] ?>"><input type="hidden" name="status" value="<?= $st ?>">
<input type="hidden" name="f_doctor" value="<?= e($fd) ?>"><input type="hidden" name="f_patient" value="<?= e($fp) ?>"><input type="hidden" name="f_date" value="<?= e($fdate) ?>"><button class="btn sm <?= $cl ?>"><?= $st==='Completed'?'Complete':'Cancel' ?></button></form>
<?php endforeach; endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
</div>
<?php include 'includes/footer.php'; ?>
