<?php require 'config.php'; $title = 'Patients';
$p = ['patient_id'=>'','name'=>'','phone'=>'','email'=>'','date_of_birth'=>'','gender'=>'']; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $p = array_map('trim', array_intersect_key($_POST, $p)) + $p;
  if ($p['name'] === '' || strlen($p['name']) > 100) $errors[] = 'Name is required (max 100 characters).';
  if (!valid_phone($p['phone'])) $errors[] = 'Phone must be 7-15 digits (numbers, +, - allowed).';
  if (!valid_email($p['email'])) $errors[] = 'Enter a valid email address.';
  if ($p['date_of_birth'] !== '') { $t = strtotime($p['date_of_birth']); if (!$t || $t > time()) $errors[] = 'Date of birth must be a valid past date.'; }
  if (!in_array($p['gender'], ['', 'Male', 'Female', 'Other'], true)) $errors[] = 'Choose a valid gender.';
  if (!$errors) {
    $v = [$p['name'], $p['phone'], $p['email'], $p['date_of_birth'] ?: null, $p['gender'] ?: null];
    if ($p['patient_id']) { $pdo->prepare("UPDATE patients SET name=?,phone=?,email=?,date_of_birth=?,gender=? WHERE patient_id=?")->execute([...$v, $p['patient_id']]); flash('Patient updated.'); }
    else { $pdo->prepare("INSERT INTO patients (name,phone,email,date_of_birth,gender) VALUES (?,?,?,?,?)")->execute($v); flash('Patient registered.'); }
    redirect('patients.php');
  }
} elseif (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?"); $s->execute([(int)$_GET['edit']]); $p = $s->fetch() ?: $p;
}
$q = trim($_GET['q'] ?? '');
$s = $pdo->prepare("SELECT * FROM patients WHERE name LIKE ? OR phone LIKE ? ORDER BY name"); $s->execute(["%$q%", "%$q%"]); $rows = $s->fetchAll();
include 'includes/header.php'; ?>
<h1>Patients</h1>
<div class="grid">
<section class="card"><h2><?= $p['patient_id'] ? 'Edit patient' : 'Register patient' ?></h2>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate data-validate><input type="hidden" name="patient_id" value="<?= e($p['patient_id']) ?>">
  <label>Full name<input name="name" required maxlength="100" value="<?= e($p['name']) ?>"></label>
  <label>Phone<input name="phone" type="tel" maxlength="15" pattern="[0-9+\-\s]{7,15}" value="<?= e($p['phone']) ?>"></label>
  <label>Email<input name="email" type="email" maxlength="100" value="<?= e($p['email']) ?>"></label>
  <label>Date of birth<input name="date_of_birth" type="date" max="<?= date('Y-m-d') ?>" value="<?= e($p['date_of_birth']) ?>"></label>
  <label>Gender<select name="gender"><option value="">Select</option><?php foreach (['Male','Female','Other'] as $g): ?><option <?= $p['gender']===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?></select></label>
  <div class="row"><button class="btn"><?= $p['patient_id'] ? 'Save changes' : 'Register patient' ?></button>
  <?php if ($p['patient_id']): ?><a class="btn ghost" href="patients.php">Cancel</a><?php endif; ?></div>
</form></section>
<section class="card"><h2>All patients (<?= count($rows) ?>)</h2>
<form class="filters" method="get"><input name="q" placeholder="Search name or phone" value="<?= e($q) ?>"><button class="btn">Search</button><?php if ($q): ?><a class="btn ghost" href="patients.php">Clear</a><?php endif; ?></form>
<?php if (!$rows): ?><p class="empty">No patients found.</p><?php else: ?><div class="scroll"><table><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>DOB</th><th>Gender</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['date_of_birth']) ?></td><td><?= e($r['gender']) ?></td>
<td class="act"><a class="btn sm ghost" href="?edit=<?= $r['patient_id'] ?>">Edit</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
</div>
<?php include 'includes/footer.php'; ?>
