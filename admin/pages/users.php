<?php
require '../config/koneksi.php';
$data = $conn->query("SELECT * FROM users");
?>

<h2>👥 Kelola Pengguna</h2>

<div class="card">
<table>
<tr>
  <th>Username</th>
  <th>Email</th>
  <th>Role</th>
</tr>

<?php while($u = $data->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($u['username']) ?></td>
  <td><?= htmlspecialchars($u['email']) ?></td>
  <td><?= strtoupper($u['role']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
