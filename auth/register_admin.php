<?php
include '../config/koneksi.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $password = $_POST['password'];

    
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users_admin (username, password, nama_lengkap) 
              VALUES ('$username', '$password_hashed', '$nama_lengkap')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Admin berhasil didaftarkan!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<form method="POST">
    <h2>Register Admin</h2>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="register">Daftar Admin</button>
</form>