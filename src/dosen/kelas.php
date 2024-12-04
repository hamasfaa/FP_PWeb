<?php
session_start();
include('../../assets/db/config.php');
include('../../auth/aksesDosen.php');

$userID = $_SESSION['U_ID'];
$sql = "SELECT U_Nama, U_Role, U_Foto FROM User WHERE U_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->store_result();

$error = '';

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $role, $photo);
    $stmt->fetch();
} else {
    header('Location: ../home/login.php');
    exit();
}

$class_sql =
    "SELECT K.K_ID,K.K_NamaKelas, K.K_TanggalDibuat, K.K_MataKuliah, K.K_KodeKelas FROM Kelas K JOIN User_Kelas UK ON K.K_ID = UK.Kelas_K_ID WHERE UK.User_U_ID = ?";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->bind_param('i', $userID);
$class_stmt->execute();
$class_stmt->store_result();

if ($class_stmt->num_rows > 0) {
    $class_stmt->bind_result($kelasID, $namaKelas, $tanggalDibuat, $mataKuliah, $kodeKelas);
} else {
    $namaKelas = $tanggalDibuat = $mataKuliah = $kodeKelas = '';
}

if (isset($_POST['kelasDEL']) && !empty($_POST['kelasDEL'])) {
    $kelasKode = $_POST['kelasDEL'];
    // $error = $kelasID;

    $check_sql = "SELECT K.K_ID FROM Kelas K JOIN User_Kelas UK ON K.K_ID = UK.Kelas_K_ID WHERE K.K_KodeKelas = ? AND UK.User_U_ID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('si', $kelasKode, $userID);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $check_stmt->bind_result($kelasID);
        $check_stmt->fetch();

        $conn->begin_transaction();


        try {
            // $error = 'masuk cuy';
            $delete_user_kelas_sql = "DELETE FROM User_Kelas WHERE Kelas_K_ID = ?";
            $delete_user_kelas_stmt = $conn->prepare($delete_user_kelas_sql);
            $delete_user_kelas_stmt->bind_param('i', $kelasID);
            $delete_user_kelas_stmt->execute();

            $delete_kelas_sql = "DELETE FROM Kelas WHERE K_ID = ?";
            $delete_kelas_stmt = $conn->prepare($delete_kelas_sql);
            $delete_kelas_stmt->bind_param('i', $kelasID);
            $delete_kelas_stmt->execute();

            $conn->commit();

            header('Location: ./kelas.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
    $check_stmt->close();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KelasKu</title>
    <link rel="stylesheet" href="../../assets/output.css">
    <style>
        .load {
            animation: transitionIn 0.75s;
        }

        @keyframes transitionIn {
            from {
                opacity: 0;
                transform: rotateX(-10deg);
            }

            to {
                opacity: 1;
                transform: rotateX(0);
            }
        }

        #sidebar {
            transition: width 0.3s ease;
            overflow: visible;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        .sidebar-collapsed .link-text,
        .sidebar-collapsed .profile-text {
            display: none;
        }

        .sidebar-collapsed .menu-item {
            justify-content: center;
        }

        .sidebar-collapsed .hamburger {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar-collapsed .profile-container {
            flex-direction: column;
            align-items: center;
        }

        .profile-container img {
            object-fit: cover;
            width: 50px;
            height: 50px;
        }

        .menu-item,
        .hamburger,
        .profile-container {
            transition: all 0.3s ease;
        }

        .menu-item {
            position: relative;
        }

        .menu-item .tooltip {
            position: absolute;
            right: 100%;
            margin-right: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: rgba(55, 65, 81, 1);
            color: rgba(255, 255, 255, 1);
            font-size: 0.875rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            white-space: nowrap;
            z-index: 1000;
        }

        .sidebar-collapsed .menu-item:hover .tooltip {
            opacity: 1;
        }

        /* Sidebar tersembunyi pada mobile */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(100%);
                /* Sembunyikan sidebar di luar layar kanan */
                width: 50%;
                /* Lebar sidebar pada mobile, sesuaikan jika diperlukan */
            }

            /* Sidebar terlihat saat memiliki kelas 'active' */
            #sidebar.active {
                transform: translateX(0);
            }

            /* Sembunyikan teks pada sidebar untuk mobile */
            .profile-container,
            .tooltip {
                display: none;
            }

            /* Tampilkan hamburger-mobile dan closeSidebar-mobile pada mobile */
            #hamburger-mobile,
            #closeSidebar-mobile {
                display: block;
            }

            /* Sembunyikan ikon hamburger default di sidebar pada mobile */
            .hamburger {
                display: none;
            }
        }

        /* Sidebar terlihat pada desktop */
        @media (min-width: 769px) {

            #hamburger-mobile,
            #closeSidebar-mobile {
                display: none;
            }
        }

        /* Tambahkan animasi buka tutup untuk sidebar di mode mobile */
        @media (max-width: 768px) {
            #sidebar {
                transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
                opacity: 0;
                /* Sidebar tersembunyi secara default */
            }

            /* Sidebar terlihat saat memiliki kelas 'active' */
            #sidebar.active {
                opacity: 1;
                /* Sidebar terlihat */
            }
        }
    </style>
</head>

<body class="font-poppins">
    <!-- NAV -->
    <nav class="flex flex-col md:flex-row md:items-center justify-between p-10 text-light-teal w-full">
        <div class="flex items-center justify-between w-full md:w-auto">
            <a href="../dosen/index.php" class="font-modak text-4xl text-dark-teal">KelasKu</a>
            <!-- Ikon Hamburger untuk Mobile -->
            <div class="md:hidden">
                <span id="hamburger-mobile" class="material-symbols-outlined text-3xl cursor-pointer">
                    menu
                </span>
            </div>
        </div>
        <div class="w-full mt-4 md:mt-0 md:flex md:justify-center">
            <div class="flex items-center border rounded p-2 md:p-4 w-full md:w-2/5 lg:w-1/4">
                <span class="material-symbols-outlined mr-2 text-light-teal">
                    search
                </span>
                <input type="text" name="search" placeholder="CARI BLABLABLA" class="flex-1 outline-none">
            </div>
        </div>
    </nav>
    <!-- SIDEBAR -->
    <div id="sidebar"
        class="fixed top-0 right-0 h-full md:w-1/6 bg-dark-teal transform translate-x-full md:translate-x-0 transition-transform duration-300 z-50 bg-opacity-90 shadow-lg flex flex-col">
        <!-- Ikon Hamburger untuk Mobile (Berfungsi Sebagai Tombol Close) -->
        <div class="text-white px-6 py-2 cursor-pointer flex md:hidden">
            <span id="closeSidebar-mobile" class="material-symbols-outlined text-3xl">
                menu
            </span>
        </div>
        <!-- Ikon Hamburger Default di Sidebar untuk Desktop (Collapse) -->
        <div class="hamburger text-white px-6 py-2 cursor-pointer md:flex hidden">
            <span class="material-symbols-outlined text-3xl">menu</span>
        </div>
        <div>
            <ul class="flex flex-col space-y-6 px-6 pt-2 pb-6 text-white">
                <li>
                    <a href="../dosen/index.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">home</span>
                        <span class="link-text ml-3">Beranda</span>
                        <span class="tooltip">Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="../dosen/kelas.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">school</span>
                        <span class="link-text ml-3">Kelas</span>
                        <span class="tooltip">Kelas</span>
                    </a>
                </li>
                <li>
                    <a href="../dosen/tugas.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">task</span>
                        <span class="link-text ml-3">Tugas</span>
                        <span class="tooltip">Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="../dosen/presensi.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">overview</span>
                        <span class="link-text ml-3">Presensi</span>
                        <span class="tooltip">Presensi</span>
                    </a>
                </li>
                <li>
                    <a href="../pengaturan.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">settings</span>
                        <span class="link-text ml-3">Pengaturan</span>
                        <span class="tooltip">Pengaturan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative" onclick="confirmLogout(event)">
                        <span class="material-symbols-outlined text-light-teal text-3xl">logout</span>
                        <span class="link-text ml-3">Keluar</span>
                        <span class="tooltip">Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Profil -->
        <div class="profile-container flex items-center space-x-4 p-6 mt-auto">
            <img src="<?php echo $photo ?>" alt="Foto Profil" class="rounded-xl w-12 h-12">
            <div class="flex flex-col profile-text">
                <span class="font-bold text-xl text-white"><?php echo htmlspecialchars($name); ?></span>
                <span class="text-white"><?php echo htmlspecialchars(strtoupper($role)); ?></span>
            </div>
        </div>
    </div>
    <!-- UTAMA -->
    <div class="w-full md:w-5/6 load p-6">
        <div class="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-row justify-between">
            <div class="header mb-4">
                <h1 class="text-3xl font-bold text-dark-teal uppercase mb-2">Atur Kelas</h1>
                <p class="text-xl text-teal-600 italic">Kelola kelasmu dengan mudah dan efisien</p>
            </div>
            <a href="tambahKelas.php"
                class="bg-dark-teal text-white text-lg px-4 py-2 h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal transition duration-300">Tambah
                Kelas</a>
        </div>
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="overflow-x-auto">
                <table class="w-full mt-6 border-collapse">
                    <thead>
                        <tr class="text-dark-teal">
                            <th class="border-b p-4 text-left font-medium">Kelas</th>
                            <th class="border-b p-4 text-left font-medium">Dibuat Pada</th>
                            <th class="border-b p-4 text-left font-medium">Mata Kuliah</th>
                            <th class="border-b p-4 text-left font-medium">Kode</th>
                            <th class="border-b p-4 text-left font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($class_stmt->fetch()): ?>
                            <tr class="transition duration-300 hover:bg-teal-50">
                                <td class="p-4"><a href="./detailKelas.php?ID=<?php echo $kelasID; ?>"><?php echo htmlspecialchars($namaKelas); ?></a></td>
                                <td class="p-4"><?php echo date('d F Y', strtotime($tanggalDibuat)); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($mataKuliah); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($kodeKelas); ?></td>
                                <td class="p-4">
                                    <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                        <input type="hidden" name="kelasDEL" value="<?php echo htmlspecialchars($kodeKelas); ?>">
                                        <button class="relative bg-red-700 text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-red-500 hover:text-red-500 cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.getElementById('sidebar');
        const hamburgerMobile = document.getElementById('hamburger-mobile');
        const closeSidebarMobile = document.getElementById('closeSidebar-mobile');

        // Fungsi untuk meng-toggle sidebar pada desktop (collapse)
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
        });

        // Fungsi untuk toggle sidebar pada mobile
        function toggleSidebar(e) {
            e.stopPropagation(); // Mencegah event bubbling
            sidebar.classList.toggle('active');
        }

        // Event listeners untuk hamburger di navbar dan tombol close di sidebar
        hamburgerMobile.addEventListener('click', toggleSidebar);
        closeSidebarMobile.addEventListener('click', toggleSidebar);

        // Menutup sidebar saat mengklik di luar sidebar pada mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) { // Hanya berlaku pada mobile
                if (!sidebar.contains(event.target) && !hamburgerMobile.contains(event.target) && !closeSidebarMobile.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Mencegah penutupan sidebar saat mengklik di dalam sidebar
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        function confirmLogout(event) {
            event.preventDefault(); // Mencegah link untuk navigasi
            const confirmation = confirm("Apakah Anda ingin keluar?");

            if (confirmation) {
                window.location.href = '../../auth/logout.php';
            } else {
                return;
            }
        }
    </script>
</body>

</html>

<?php
$class_stmt->close();
?>