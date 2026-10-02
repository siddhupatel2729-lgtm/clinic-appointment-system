<?php require 'config.php'; $title = 'Doctors';
$d = ['doctor_id'=>'','name'=>'','specialization'=>'','phone'=>'','email'=>'']; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'delete') {
    try { $pdo->prepare("DELETE FROM doctors WHERE doctor_id=?")->execute([(int)$_POST['id']]); flash('Doctor deleted.'); }
    catch (PDOException $e) { flash('Cannot delete: this doctor has appointments. Cancel or remove them first.', 'error'); }
    redirect('doctors.php');
  }
  $d = array_map('trim', array_intersect_key($_POST, $d)) + $d;
  if ($d['name'] === '' || strlen($d['name']) > 100) $errors[] = 'Name is required (max 100 characters).';
  if ($d['specialization'] === '') $errors[] = 'Specialization is required.';
  if (!valid_phone($d['phone'])) $errors[] = 'Phone must be 7-15 digits (numbers, +, - allowed).';
  if (!valid_email($d['email'])) $errors[] = 'Enter a valid email address.';
  if (!$errors) {
    $v = [$d['name'], $d['specialization'], $d['phone'], $d['email']];
    if ($d['doctor_id']) { $pdo->prepare("UPDATE doctors SET name=?,specialization=?,phone=?,email=? WHERE doctor_id=?")->execute([...$v, $d['doctor_id']]); flash('Doctor updated.'); }
    else { $pdo->prepare("INSERT INTO doctors (name,specialization,phone,email) VALUES (?,?,?,?)")->execute($v); flash('Doctor added.'); }
    redirect('doctors.php');
  }
} elseif (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id=?"); $s->execute([(int)$_GET['edit']]); $d = $s->fetch() ?: $d;
}
$q = trim($_GET['q'] ?? '');
$s = $pdo->prepare("SELECT * FROM doctors WHERE name LIKE ? OR specialization LIKE ? ORDER BY name"); $s->execute(["%$q%", "%$q%"]); $rows = $s->fetchAll();
include 'includes/header.php'; ?>
<h1>Doctors</h1>
<div class="grid">
<section class="card"><h2><?= $d['doctor_id'] ? 'Edit doctor' : 'Add doctor' ?></h2>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate data-validate><input type="hidden" name="doctor_id" value="<?= e($d['doctor_id']) ?>">
  <label>Full name<input name="name" required maxlength="100" value="<?= e($d['name']) ?>"></label>
  <label>Specialization<input name="specialization" required maxlength="100" value="<?= e($d['specialization']) ?>" list="specs">
    <datalist id="specs"><option>General Physician<option>Cardiology<option>Dermatology<option>Pediatrics<option>Orthopedics<option>Neurology<option>Gynecology<option>ENT</datalist></label>
  <label>Phone<input name="phone" type="tel" maxlength="15" pattern="[0-9+\-\s]{7,15}" value="<?= e($d['phone']) ?>"></label>
  <label>Email<input name="email" type="email" maxlength="100" value="<?= e($d['email']) ?>"></label>
  <div class="row"><button class="btn"><?= $d['doctor_id'] ? 'Save changes' : 'Add doctor' ?></button>
  <?php if ($d['doctor_id']): ?><a class="btn ghost" href="doctors.php">Cancel</a><?php endif; ?></div>
</form></section>
<section class="card"><h2>All doctors (<?= count($rows) ?>)</h2>
<form class="filters" method="get"><input name="q" placeholder="Search name or specialization" value="<?= e($q) ?>"><button class="btn">Search</button><?php if ($q): ?><a class="btn ghost" href="doctors.php">Clear</a><?php endif; ?></form>
<?php if (!$rows): ?><p class="empty">No doctors found.</p><?php else: ?><div class="scroll"><table><thead><tr><th>Name</th><th>Specialization</th><th>Phone</th><th>Email</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['specialization']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td>
<td class="act"><a class="btn sm ghost" href="?edit=<?= $r['doctor_id'] ?>">Edit</a>
<form method="post" data-confirm="Delete <?= e($r['name']) ?>?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['doctor_id'] ?>"><button class="btn sm danger">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
</div>
<?php include 'includes/footer.php'; ?>
