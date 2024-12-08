<?php
include('../../assets/db/config.php');


header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userID = $data['userID'];
    $search = $data['search'];

    $search_sql = "
        SELECT 
            K.K_MataKuliah, K.K_NamaKelas,
            AD.AD_TanggalDibuat, AD.AD_Deskripsi, 
            TD.TD_Judul, TD.TD_Deadline
        FROM 
            Kelas K
        LEFT JOIN 
            Absen_Dosen AD ON K.K_ID = AD.Kelas_K_ID
        LEFT JOIN 
            Tugas_Dosen TD ON K.K_ID = TD.Kelas_K_ID
        LEFT JOIN 
            User_Kelas UK ON UK.Kelas_K_ID = K.K_ID
        WHERE 
            UK.User_U_ID = ? AND (
                K.K_MataKuliah LIKE ? OR
                K.K_NamaKelas LIKE ? OR
                AD.AD_Deskripsi LIKE ? OR
                TD.TD_Judul LIKE ?
            )
        ORDER BY K.K_TanggalDibuat DESC
    ";

    $stmt_search = $conn->prepare($search_sql);
    $ketentuan = '%' . $search . '%';
    $stmt_search->bind_param('issss', $userID, $ketentuan, $ketentuan, $ketentuan, $ketentuan);
    $stmt_search->execute();
    $hasil = $stmt_search->get_result();

    $data = [];
    while ($row = $hasil->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} else {
    // echo json_encode(['error' => 'Invalid request method']);
    echo $_SERVER['REQUEST_METHOD'];
}
