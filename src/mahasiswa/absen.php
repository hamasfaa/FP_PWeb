<?php
session_start();
include('../../assets/db/config.php');
include('../../auth/aksesMahasiswa.php');

$userID = $_SESSION['U_ID'];

// Mengambil informasi pengguna (dosen)
$sql_profile = "SELECT U_Nama, U_Role, U_Foto FROM User WHERE U_ID = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param('i', $userID);
$stmt_profile->execute();
$stmt_profile->store_result();

$error_message = '';

if ($stmt_profile->num_rows > 0) {
    $stmt_profile->bind_result($name, $role, $photo);
    $stmt_profile->fetch();
} else {
    header('Location: ../home/login.php');
    exit();
}

$stmt_profile->close();

// Menentukan kelas yang akan ditampilkan
if (isset($_GET['kelas_id'])) {
    $kelasID = intval($_GET['kelas_id']);
} else {
    // Handle error jika kelas_id tidak ditentukan
    echo "Kelas tidak ditentukan.";
    exit();
}

// Mengambil informasi kelas
$sql_kelas = "SELECT K_NamaKelas, K_MataKuliah FROM Kelas WHERE K_ID = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param('i', $kelasID);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();

if ($result_kelas->num_rows > 0) {
    $kelas = $result_kelas->fetch_assoc();
    $namaKelas = $kelas['K_NamaKelas'];
    $mataKuliah = $kelas['K_MataKuliah'];
} else {
    // Handle jika kelas tidak ditemukan
    echo "Kelas tidak ditemukan.";
    exit();
}

$stmt_kelas->close();

// Mengambil daftar dosen terkait kelas tersebut
$sql_dosen = "SELECT U.U_Nama 
              FROM User U
              INNER JOIN User_Kelas UK ON U.U_ID = UK.User_U_ID
              WHERE UK.Kelas_K_ID = ? AND U.U_Role = 'dosen'";
$stmt_dosen = $conn->prepare($sql_dosen);
$stmt_dosen->bind_param('i', $kelasID);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();

$dosen_names = [];
while ($row = $result_dosen->fetch_assoc()) {
    $dosen_names[] = $row['U_Nama'];
}

$stmt_dosen->close();
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

        /* Style untuk modal (overlay) */
        .modal {
            display: none;
            /* Tersembunyi secara default */
            position: fixed;
            /* Tetap di tempat */
            z-index: 1;
            /* Berada di atas */
            left: 0;
            top: 0;
            width: 100%;
            /* Lebar penuh */
            height: 100%;
            /* Tinggi penuh */
            overflow: auto;
            /* Aktifkan scroll jika diperlukan */
            background-color: rgba(0, 0, 0, 0.4);
            /* Hitam dengan opasitas */
        }

        /* Tombol Tutup */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: red;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body class="font-poppins">
    <!-- NAV -->
    <nav class="flex flex-col md:flex-row md:items-center justify-between p-10 text-light-teal w-full">
        <div class="flex items-center justify-between w-full md:w-auto">
            <a href="../home/login.php" class="font-modak text-4xl text-dark-teal">KelasKu</a>
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
                    <a href="../mahasiswa/index.php"
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
                    <a href="../mahasiswa/pengaturan.php"
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
    <div id="utama" class="w-full md:w-5/6 load">
        <div class="p-6 rounded-lg shadow-md flex flex-row justify-between">
            <div class="header mb-4">
                <!-- Menampilkan Nama Kelas dari Database -->
                <h1 class="px-4 text-3xl font-bold text-dark-teal uppercase mb-1">
                    <?php echo htmlspecialchars($mataKuliah); ?>
                </h1>

                <h2 class="px-4 text-2xl text-teal-600 font-bold mb-2">Dosen:</h2>

                <!-- Menampilkan Daftar Dosen dari Database -->
                <?php
                if (!empty($dosen_names)) {
                    foreach ($dosen_names as $dosen) {
                        echo '<p class="px-4 text-xl text-teal-600 italic mb-1">' . htmlspecialchars($dosen) . '</p>';
                    }
                } else {
                    echo '<p class="px-4 text-xl text-teal-600 italic mb-1">Tidak ada dosen yang terdaftar.</p>';
                }
                ?>
            </div>
        </div>
        <div class="p-6 rounded-lg flex flex-col md:flex-row items-center justify-center w-1/2 mx-auto h-auto bg-green-100 mt-8">
            <div class="w-full md:w-1/2">
                <table class="w-full text-center border-collapse text-lg lg:text-xl">
                    <thead class="font-bold">
                        <tr>
                            <th class="border-r border-gray-300 w-1/4 text-blue-700 py-2">HADIR</th>
                            <th class="border-r border-gray-300 w-1/4 text-purple-700">IZIN</th>
                            <th class="border-r border-gray-300 w-1/4 text-yellow-600">SAKIT</th>
                            <th class="w-1/4 text-red-600">ALPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-r border-gray-300 py-2">3</td>
                            <td class="border-r border-gray-300">1</td>
                            <td class="border-r border-gray-300">1</td>
                            <td>1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="w-full md:w-1/2 mt-4 md:mt-0">
                <table class="w-full text-center md:text-right border-collapse text-lg lg:text-xl">
                    <thead class="font-bold">
                        <tr>
                            <th class="py-2">TOTAL TATAP MUKA TERLAKSANA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2">6</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-6 rounded-lg flex flex-col md:flex-row items-center justify-center w-5/6 mx-auto h-auto bg-gray-100 mt-8">
            <table class="class-table w-full border-collapse">
                <thead>
                    <tr class="text-dark-teal w-1/5">
                        <th class="border-b p-4 text-left font-medium">Tatap muka</th>
                        <th class="border-b p-4 text-left font-medium">Jadwal</th>
                        <th class="border-b p-4 text-left font-medium">Dosen</th>
                        <th class="border-b p-4 text-left font-medium">Status</th>
                        <th class="border-b p-4 text-left font-medium">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="transition">
                        <td class="p-4">1</td>
                        <td class="p-4">12 Agustus 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-blue-700 font-bold">HADIR</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                    <tr class="transition">
                        <td class="p-4">2</td>
                        <td class="p-4">19 Agustus 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-purple-700 font-bold">IZIN</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                    <tr class="transition">
                        <td class="p-4">3</td>
                        <td class="p-4">26 Agustus 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-yellow-600 font-bold">SAKIT</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                    <tr class="transition">
                        <td class="p-4">4</td>
                        <td class="p-4">2 September 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-blue-700 font-bold">HADIR</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                    <tr class="transition">
                        <td class="p-4">5</td>
                        <td class="p-4">9 September 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-blue-700 font-bold">HADIR</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                    <tr class="transition">
                        <td class="p-4">6</td>
                        <td class="p-4">16 September 2024</td>
                        <td class="p-4">Bintang Nuralamsyah, S.Kom., M.Kom.</td>
                        <td class="p-4 text-red-600 font-bold">ALPA</td>
                        <td class="p-4">
                            <button
                                class="relative bg-dark-teal text-white text-lg px-4 py-2 w-fit h-fit rounded-xl border hover:bg-white hover:border-light-teal hover:text-light-teal"
                                onclick="openModal()">Kode
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="myModal" class="modal">
            <form class="modal-content bg-white p-5 border border-gray-300 rounded-lg shadow-lg mx-auto mt-20 w-1/3" action="submit_path" method="POST">
                <span class="close text-2xl font-bold text-gray-500 cursor-pointer float-right" onclick="closeModal()">&times;</span>
                <div class="mt-5">
                    <label for="code" class="block text-sm font-medium text-gray-700">Masukkan 6 Digit Kode Presensi: <span class="text-red-500">*</span></label>
                    <input type="text" id="code" name="code" required minlength="6" maxlength="6" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-4 mb-2 text-sm font-medium text-gray-700">Kehadiran Kuliah <span class="text-red-500">*</span></p>
                    <div class="flex items-center mb-4">
                        <input type="radio" id="class" name="attendance" value="class" required class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <label for="class" class="ml-2 text-sm text-gray-700">Saya hadir kuliah di kelas</label>
                    </div>
                    <div class="flex items-center mb-4">
                        <input type="radio" id="online" name="attendance" value="online" required class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <label for="online" class="ml-2 text-sm text-gray-700">Saya hadir kuliah secara online</label>
                    </div>
                    <button type="submit" class="w-full bg-dark-teal hover:bg-light-teal text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Simpan</button>
                </div>
            </form>
        </div>
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

        const utama = document.getElementById('utama');

        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');

            if (sidebar.classList.contains('sidebar-collapsed')) {
                utama.classList.remove('md:w-5/6');
                utama.classList.add('w-full');
            } else {
                utama.classList.remove('w-full');
                utama.classList.add('md:w-5/6');
            }
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

        // Mendapatkan modal
        var modal = document.getElementById("myModal");

        // Mendapatkan tombol yang membuka modal
        var btn = document.querySelector(".open-modal-btn");

        // Fungsi untuk membuka modal
        function openModal() {
            modal.style.display = "block";
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            modal.style.display = "none";
        }
    </script>
</body>

</html>