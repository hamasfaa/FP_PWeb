<?php
// session_start();

if (isset($_SESSION['U_ID'])) {
    if ($_SESSION['U_ROLE'] == 'dosen') {
        header("Location: ../dosen/index.php");
    } else if ($_SESSION['U_ROLE'] == 'mahasiswa') {
        header("Location: ../mahasiswa/index.php");
    }
}
exit();
