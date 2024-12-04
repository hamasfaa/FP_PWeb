<?php
session_start();
include('../../assets/db/config.php');
include('../../auth/aksesMahasiswa.php');

$userID = $_SESSION['U_ID']; // Pastikan session sudah menyimpan U_ID
$sql = "SELECT U_Nama, U_Role, U_Foto FROM User WHERE U_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $role, $photo);
    $stmt->fetch();
} else {
    header('Location: ../home/login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kodeKelas = $_POST['classCode'];

    // Periksa apakah kelas dengan kode tersebut ada
    $query = "SELECT K.K_ID, K.K_NamaKelas, K.K_MataKuliah FROM Kelas K WHERE K.K_KodeKelas = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $kodeKelas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $kelas = $result->fetch_assoc();
        $kelasId = $kelas['K_ID'];
        
        // Periksa apakah mahasiswa sudah terdaftar di kelas ini
        $checkQuery = "SELECT * FROM KelasMahasiswa WHERE Kelas_K_ID = ? AND User_U_ID = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $kelasId, $userID); // Ganti $userId menjadi $userID
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            // Tambahkan mahasiswa ke kelas
            $insertQuery = "INSERT INTO KelasMahasiswa (Kelas_K_ID, User_U_ID, TanggalAmbil) VALUES (?, ?, NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ii", $kelasId, $userID); // Ganti $userId menjadi $userID
            if ($insertStmt->execute()) {
                $message = "Berhasil mengambil kelas: " . $kelas['K_NamaKelas'];
            } else {
                $message = "Gagal mengambil kelas. Silakan coba lagi.";
            }
            $insertStmt->close();
        } else {
            $message = "Anda sudah terdaftar di kelas ini.";
        }
        $checkStmt->close();
    } else {
        $message = "Kode kelas tidak valid.";
    }
    $stmt->close();
}

$conn->close();
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
                transform: translateX(100%); /* Sembunyikan sidebar di luar layar kanan */
                width: 50%; /* Lebar sidebar pada mobile, sesuaikan jika diperlukan */
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
                opacity: 0; /* Sidebar tersembunyi secara default */
            }

            /* Sidebar terlihat saat memiliki kelas 'active' */
            #sidebar.active {
                opacity: 1; /* Sidebar terlihat */
            }
        }
        
    </style>
</head>

<body class="font-poppins">
    <!-- NAV -->
    <nav class="flex flex-col md:flex-row md:items-center justify-between p-10 text-light-teal w-full">
        <div class="flex items-center justify-between w-full md:w-auto">
            <a href="../home/login.html" class="font-modak text-4xl text-dark-teal">KelasKu</a>
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
        <div class="hamburger text-white px-6 py-2 cursor-pointer flex md:flex hidden">
            <span class="material-symbols-outlined text-3xl">menu</span>
        </div>
        <div>
            <ul class="flex flex-col space-y-6 px-6 pt-2 pb-6 text-white">
                <li>
                    <a href="../mahasiswa/beranda.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">home</span>
                        <span class="link-text ml-3">Beranda</span>
                        <span class="tooltip">Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="../mahasiswa/kelas.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">school</span>
                        <span class="link-text ml-3">Kelas</span>
                        <span class="tooltip">Kelas</span>
                    </a>
                </li>
                <li>
                    <a href="../mahasiswa/nilai.php"
                        class="flex items-center hover:-translate-y-1 transition menu-item text-xl relative">
                        <span class="material-symbols-outlined text-light-teal text-3xl">monitoring</span>
                        <span class="link-text ml-3">Penilaian</span>
                        <span class="tooltip">Penilaian</span>
                    </a>
                </li>
                <li>
                    <a href="../mahasiswa/presensi.php"
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

    </div>
    <!-- UTAMA -->
    <div class="p-6 rounded-lg shadow-md flex flex-row justify-between load">
        <div class="header mb-4">
            <h1 class="px-4 text-3xl font-bold text-dark-teal uppercase mb-2">Ambil Kelas</h1>
            <p class="px-4 text-xl text-teal-600 italic">Masukkan kode kelas yang Anda ingin ambil</p>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="px-4 py-2 text-center <?= strpos($message, 'Berhasil') !== false ? 'text-green-500' : 'text-red-500' ?>">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="p-6 rounded-lg load">
        <form method="POST">
            <div class="mb-4 px-4">
                <label for="classCode" class="block text-gray-700 font-bold mb-2 text-xl">Kode Unik Kelas:</label>
                <input type="text" id="classCode" name="classCode"
                    class="px-3 shadow appearance-none border rounded w-full md:w-1/3 py-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Masukkan kode kelas" required>
            </div>
            <div class="flex items-center justify-between px-4">
                <button type="submit"
                        class="bg-dark-teal hover:bg-teal-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto">Ambil Kelas
                </button>
            </div>
        </form>
    </div>
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Mencegah link untuk navigasi
            const confirmation = confirm("Apakah Anda ingin keluar?");

            if (confirmation) {
                window.location.href = '../../auth/logout.php';
            } else {
                return;
            }
        }
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.getElementById('sidebar');
        const hamburgerMobile = document.getElementById('hamburger-mobile');
        const closeSidebarMobile = document.getElementById('closeSidebar-mobile');

        // Fungsi untuk meng-toggle sidebar pada desktop (collapse)
        hamburger.addEventListener('click', function () {
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
        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 768) { // Hanya berlaku pada mobile
                if (!sidebar.contains(event.target) && !hamburgerMobile.contains(event.target) && !closeSidebarMobile.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Mencegah penutupan sidebar saat mengklik di dalam sidebar
        sidebar.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    </script>
</body>

</html>
