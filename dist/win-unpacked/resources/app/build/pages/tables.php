<?php
include '../config/db.php'; // ganti sesuai koneksi kamu

if (isset($_POST['simpan_jurnal'])) {
  $id_jadwal = $_POST['id_jadwal'];
  $jurnal = trim($_POST['jurnal']);

  // biar kalau kosong tetap masuk NULL
  $jurnal = ($jurnal === '') ? null : $jurnal;

  $stmt = $conn->prepare("INSERT INTO jurnal (id_jadwal, catatan) VALUES (:id_jadwal, :jurnal)");
  $stmt->bindParam(':id_jadwal', $id_jadwal, PDO::PARAM_INT);
  $stmt->bindParam(':jurnal', $jurnal, PDO::PARAM_STR);
  $stmt->execute();

  echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotif('Jurnal berhasil disimpan!');
  });
  window.history.replaceState(null, null, window.location.pathname);
</script>";
}

if (isset($_POST['simpan_presensi'])) {
  $id_jadwal  = $_POST['id_jadwal'];
  $status     = $_POST['status'];
  $keterangan = !empty($_POST['keterangan']) ? $_POST['keterangan'] : null;

  $stmt = $conn->prepare("INSERT INTO presensi (id_jadwal, status, keterangan) VALUES (:id_jadwal, :status, :keterangan)");
  $stmt->bindParam(':id_jadwal', $id_jadwal, PDO::PARAM_INT);
  $stmt->bindParam(':status', $status, PDO::PARAM_STR);
  $stmt->bindParam(':keterangan', $keterangan, PDO::PARAM_STR);
  $stmt->execute();

  echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotif('Presensi berhasil disimpan!');
  });
  window.history.replaceState(null, null, window.location.pathname);
</script>";
}

if (isset($_POST['simpan_tugas'])) {
  $id_matkul = $_POST['id_matkul'];
  $judul     = trim($_POST['judul']);
  $deskripsi = !empty($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
  $deadline  = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
  $foto_opsional = [];

  // upload beberapa file kalau ada
  if (!empty($_FILES['foto_opsional']['name'][0])) {
    $targetDir = __DIR__ . "/uploads/tugas/";
    $publicDir = "uploads/tugas/"; // untuk disimpan di DB (akses dari browser)

    // pastikan folder ada
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    foreach ($_FILES['foto_opsional']['name'] as $i => $name) {
      $ext = pathinfo($name, PATHINFO_EXTENSION);
      $fileName = time() . "_" . uniqid() . "." . $ext;
      $targetFile = $targetDir . $fileName;

      if (move_uploaded_file($_FILES["foto_opsional"]["tmp_name"][$i], $targetFile)) {
        $foto_opsional[] = $publicDir . $fileName;
        // simpan path relatif utk nanti ditampilkan
      }
    }
  }

  // jadikan string / json
  $foto_opsional_json = !empty($foto_opsional) ? json_encode($foto_opsional) : null;

  $stmt = $conn->prepare("
      INSERT INTO tugas (id_matkul, judul, deskripsi, deadline, foto_opsional)
      VALUES (:id_matkul, :judul, :deskripsi, :deadline, :foto_opsional)
  ");
  $stmt->bindParam(':id_matkul', $id_matkul, PDO::PARAM_INT);
  $stmt->bindParam(':judul', $judul, PDO::PARAM_STR);
  $stmt->bindParam(':deskripsi', $deskripsi, PDO::PARAM_STR);
  $stmt->bindParam(':deadline', $deadline, PDO::PARAM_STR);
  $stmt->bindParam(':foto_opsional', $foto_opsional_json, PDO::PARAM_STR);
  $stmt->execute();

  echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotif('Tugas berhasil disimpan!');
  });
  window.history.replaceState(null, null, window.location.pathname);
</script>";
}

// === PROSES SIMPAN EDIT JADWAL ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_edit'])) {
  $selectedMatkuls = $_POST['id_matkul'] ?? [];
  $semesterId = (int)($_POST['semester'] ?? 0);

  if ($semesterId > 0) {
    // Hapus jadwal lama hanya untuk semester yang dipilih
    $del = $conn->prepare("
            DELETE j FROM jadwal j
            JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
            WHERE mk.id_semester = ?
        ");
    $del->execute([$semesterId]);

    // Insert ulang jadwal baru (tanpa duplikat)
    if (!empty($selectedMatkuls)) {
      $stmt = $conn->prepare("INSERT IGNORE INTO jadwal (id_matkul) VALUES (?)");
      foreach ($selectedMatkuls as $id) {
        $stmt->execute([(int)$id]);
      }
    }
  }

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!--
=========================================================
* Soft UI Dashboard Tailwind - v1.0.5
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard-tailwind
* Copyright 2023 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png" />
  <link rel="icon" type="image/png" href="../assets/img/favicon.png" />
  <title>Soft UI Dashboard Tailwind</title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Main Styling -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="../assets/css/soft-ui-dashboard-tailwind.css?v=1.0.5" rel="stylesheet" />
  <!-- <link rel="stylesheet" href="../assets/css/responsive.css"> -->

  <!-- Nepcha Analytics (nepcha.com) -->
  <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
  <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
  <!-- <style>
    .modal-overlay {
      @apply fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50;
    }

    .modal-content {
      @apply bg-white rounded-2xl p-6 w-96 shadow-lg relative;
    }

    .modal-hidden {
      @apply hidden;
    }

    .close-btn {
      @apply absolute top-2 right-2 text-gray-500 hover:text-black text-xl;
    }
  </style> -->
</head>

<body class="m-0 font-sans text-base antialiased font-normal leading-default bg-gray-50 text-slate-500">

  <!-- sidenav  -->
  <?php
  $activePage = "tables";
  include '../components/sidenav.php';
  ?>

  <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
    <!-- Navbar -->
    <nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all shadow-none duration-250 ease-soft-in rounded-2xl lg:flex-nowrap lg:justify-start" navbar-main navbar-scroll="true">
      <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
        <nav>
          <!-- breadcrumb -->
          <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
            <li class="leading-normal text-sm">
              <a class="opacity-50 text-slate-700" href="javascript:;">Pages</a>
            </li>
            <li class="text-sm pl-2 capitalize leading-normal text-slate-700 before:float-left before:pr-2 before:text-gray-600 before:content-['/']" aria-current="page">Table</li>
          </ol>
          <h6 class="mb-0 font-bold capitalize">Jadwal Kuliah</h6>
        </nav>
      </div>
    </nav>

    <!-- end Navbar -->


    <div class="w-full px-6 py-6 mx-auto">
      <!-- table 1 -->

      <?php

      // Ambil data matkul + semester
      $stmt = $conn->prepare("
    SELECT j.id_jadwal,
           mk.id_matkul, mk.nama_matkul, mk.hari, mk.jam_mulai, mk.jam_selesai, mk.ruangan,
           s.nama_semester, s.tahun_ajaran
    FROM jadwal j
    INNER JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    ORDER BY FIELD(mk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
             mk.jam_mulai
");
      $stmt->execute();
      $matkul = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
              <h6 class="text-lg font-semibold text-slate-700">Daftar Mata Kuliah</h6>

              <!-- Tombol Edit Jadwal -->
              <button onclick="openEditModal()"
                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-400 text-white font-semibold text-sm rounded-xl shadow-md hover:scale-105 transition">
                <i class="fas fa-pen mr-1"></i>
              </button>
            </div>
            <div class="flex-auto px-0 pt-0 pb-2">
              <div class="p-0 overflow-x-auto">
                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                  <thead class="align-bottom">
                    <tr>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Mata Kuliah</th>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Semester</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Hari</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Jam</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Ruangan</th>
                      <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
                    </tr>
                  </thead>
                  <?php
                  date_default_timezone_set('Asia/Jakarta'); // pastikan timezone
                  $modals = []; // inisialisasi

                  $hariSekarang = date("l");
                  $hariMap = [
                    "Sunday" => "Minggu",
                    "Monday" => "Senin",
                    "Tuesday" => "Selasa",
                    "Wednesday" => "Rabu",
                    "Thursday" => "Kamis",
                    "Friday" => "Jumat",
                    "Saturday" => "Sabtu"
                  ];

                  // ambil tanggal hari ini
                  $tanggalHariIni = date("Y-m-d");

                  // ambil semua id_jadwal yang sudah ada jurnal hari ini
                  $jurnalHariIni = $conn->query("SELECT id_jadwal FROM jurnal 
    WHERE DATE(created_at) = '$tanggalHariIni'")->fetchAll(PDO::FETCH_COLUMN);

                  // ambil semua id_jadwal yang sudah ada presensi hari ini
                  $presensiHariIni = $conn->query("SELECT id_jadwal FROM presensi 
    WHERE DATE(created_at) = '$tanggalHariIni'")->fetchAll(PDO::FETCH_COLUMN);

                  // Tugas simpan id_matkul (sesuai form)
                  $tugasHariIni = $conn->query("
  SELECT id_matkul FROM tugas 
  WHERE DATE(created_at) = '$tanggalHariIni'
")->fetchAll(PDO::FETCH_COLUMN);
                  ?>

                  <tbody>
                    <?php foreach ($matkul as $row): ?>
                      <?php $isHariIni = ($hariMap[$hariSekarang] == $row['hari']); ?>
                      <tr class="<?= $isHariIni ? 'bg-yellow-50 text-slate-700 shadow-md' : 'bg-white' ?>">
                        <td class="p-2 align-middle border-b whitespace-nowrap"></td>
                        <td class="p-2 align-middle border-b whitespace-nowrap">
                          <h6 class="mb-0 text-sm leading-normal"><?= $row['nama_matkul'] ?></h6>
                        </td>
                        <td class="p-2 align-middle border-b whitespace-nowrap">
                          <p class="mb-0 text-xs font-semibold"><?= $row['nama_semester'] ?></p>
                          <p class="mb-0 text-xs text-slate-400"><?= $row['tahun_ajaran'] ?></p>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="px-2.5 py-1 inline-block text-xs font-bold uppercase rounded-lg <?= $isHariIni ? 'bg-yellow-400 text-slate-600 shadow-soft-md' : 'bg-gray-200 text-slate-600' ?>">
                            <?= $row['hari'] ?>
                          </span>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="text-xs font-semibold text-slate-400">
                            <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                          </span>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="text-xs font-semibold text-slate-400"><?= $row['ruangan'] ?></span>
                        </td>
                        <td class="p-2 align-middle border-b whitespace-nowrap">
                          <?php if ($isHariIni): ?>
                            <div class="flex gap-2">
                              <?php if (!in_array($row['id_jadwal'], $jurnalHariIni)): ?>
                                <button onclick="openModal('modalJurnal<?= $row['id_jadwal'] ?>')" class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-blue-600 to-cyan-400 hover:scale-105">
                                  Jurnal
                                </button>
                              <?php endif; ?>

                              <?php if (!in_array($row['id_jadwal'], $presensiHariIni)): ?>
                                <button onclick="openModal('modalPresensi<?= $row['id_jadwal'] ?>')" class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-green-600 to-lime-400 hover:scale-105">
                                  Presensi
                                </button>
                              <?php endif; ?>

                              <button onclick="openModal('modalTugas<?= $row['id_jadwal'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-red-500 to-yellow-400 hover:scale-105">
                                Tugas
                              </button>
                            </div>
                          <?php endif; ?>
                        </td>
                      </tr>

                      <!-- simpan data lengkap untuk modal -->
                      <?php $modals[] = [
                        'id_jadwal' => $row['id_jadwal'],
                        'id_matkul' => $row['id_matkul']
                      ]; ?>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <style>
                  @keyframes fadeIn {
                    from {
                      opacity: 0;
                      transform: translateY(-20px)
                    }

                    to {
                      opacity: 1;
                      transform: translateY(0)
                    }
                  }

                  .animate-fade-in {
                    animation: fadeIn .3s ease-out
                  }
                </style>
                <?php if (!empty($modals)): ?>
                  <?php foreach ($modals as $m):
                    $id_jadwal = $m['id_jadwal'];
                    $id_matkul = $m['id_matkul'];
                  ?>

                    <!-- Modal Jurnal -->
                    <div id="modalJurnal<?= $id_jadwal ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalJurnal<?= $id_jadwal ?>')">
                      <div class="bg-white rounded-2xl p-8 w-full max-w-5xl shadow-2xl relative animate-fade-in"
                        onclick="event.stopPropagation()">
                        <button onclick="closeModal('modalJurnal<?= $id_jadwal ?>')"
                          class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📓 Input Jurnal</h2>
                        <form method="POST">
                          <input type="hidden" name="id_jadwal" value="<?= htmlspecialchars($id_jadwal, ENT_QUOTES) ?>">
                          <textarea name="jurnal" rows="8" class="w-full border border-gray-300 rounded-lg p-3 mb-6 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Tuliskan catatan jurnal..."></textarea>
                          <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeModal('modalJurnal<?= $id_jadwal ?>')" class="px-5 py-2.5 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700 hover:text-white font-medium transition">Batal</button>
                            <button type="submit" name="simpan_jurnal" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-slate-600 hover:text-white font-semibold shadow transition">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>

                    <!-- Modal Presensi -->
                    <div id="modalPresensi<?= $id_jadwal ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalPresensi<?= $id_jadwal ?>')">
                      <div class="bg-white rounded-2xl p-8 w-full max-w-3xl shadow-2xl relative animate-fade-in"
                        onclick="event.stopPropagation()">
                        <button onclick="closeModal('modalPresensi<?= $id_jadwal ?>')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">👥 Input Presensi</h2>
                        <form method="POST">
                          <input type="hidden" name="id_jadwal" value="<?= htmlspecialchars($id_jadwal, ENT_QUOTES) ?>">
                          <label class="block mb-2 text-sm font-medium text-gray-700">Status Kehadiran</label>
                          <select name="status" required class="w-full border border-gray-300 rounded-lg p-3 mb-6 focus:ring-2 focus:ring-green-500 focus:outline-none">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Tidak Hadir">Tidak Hadir</option>
                            <option value="Diganti">Diganti</option>
                          </select>
                          <label class="block mb-2 text-sm font-medium text-gray-700">Keterangan (opsional)</label>
                          <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg p-3 mb-6 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Catatan tambahan..."></textarea>
                          <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeModal('modalPresensi<?= $id_jadwal ?>')" class="px-5 py-2.5 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700 hover:text-white font-medium transition">Batal</button>
                            <button type="submit" name="simpan_presensi" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 rounded-lg text-slate-600 hover:text-white font-semibold shadow transition">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>

                    <!-- Modal Tugas -->
                    <div id="modalTugas<?= $id_jadwal ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalTugas<?= $id_jadwal ?>')">
                      <div class="bg-white rounded-2xl p-8 w-full max-w-4xl shadow-2xl relative animate-fade-in"
                        onclick="event.stopPropagation()">
                        <button onclick="closeModal('modalTugas<?= $id_jadwal ?>')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📌 Input Tugas</h2>
                        <form method="POST" enctype="multipart/form-data">
                          <input type="hidden" name="id_matkul" value="<?= htmlspecialchars($id_matkul, ENT_QUOTES) ?>">
                          <label class="block mb-2 text-sm font-medium text-gray-700">Judul Tugas</label>
                          <input type="text" name="judul" required class="w-full border border-gray-300 rounded-lg p-3 mb-4 focus:ring-2 focus:ring-red-500 focus:outline-none" placeholder="Judul tugas">
                          <label class="block mb-2 text-sm font-medium text-gray-700">Deskripsi</label>
                          <textarea name="deskripsi" rows="5" class="w-full border border-gray-300 rounded-lg p-3 mb-6 focus:ring-2 focus:ring-red-500 focus:outline-none" placeholder="Deskripsi tugas (opsional)..."></textarea>
                          <label class="block mb-2 text-sm font-medium text-gray-700">Deadline</label>
                          <input type="datetime-local" name="deadline" required class="w-full border border-gray-300 rounded-lg p-3 mb-6 focus:ring-2 focus:ring-red-500 focus:outline-none">
                          <label class="block mb-2 text-sm font-medium text-gray-700">Lampiran (opsional)</label>
                          <input type="file" name="foto_opsional[]" multiple accept="image/*,application/pdf" class="w-full border border-gray-300 rounded-lg p-2 mb-6 text-sm text-gray-700">
                          <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeModal('modalTugas<?= $id_jadwal ?>')" class="px-5 py-2.5 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700 hover:text-white font-medium transition">Batal</button>
                            <button type="submit" name="simpan_tugas" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 rounded-lg text-slate-600 hover:text-white font-semibold shadow transition">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <?php
                // --- seed data untuk panel kanan + selectedIds JS ---
                $jadwalStmt = $conn->prepare("
  SELECT j.id_jadwal,
         mk.id_matkul, mk.nama_matkul, mk.hari, mk.jam_mulai, mk.jam_selesai, mk.ruangan,
         s.id_semester, s.nama_semester, s.tahun_ajaran
  FROM jadwal j
  JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
  JOIN semester s ON mk.id_semester = s.id_semester
  ORDER BY FIELD(mk.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), mk.jam_mulai
");
                $jadwalStmt->execute();
                $jadwalNow = $jadwalStmt->fetchAll(PDO::FETCH_ASSOC);

                // id_matkul yang sedang aktif (buat seed ke JS)
                $selectedMatkulIds = array_map('intval', array_column($jadwalNow, 'id_matkul'));

                // --- ambil semua semester untuk dropdown kiri ---
                $semesterStmt = $conn->prepare("SELECT id_semester, nama_semester, tahun_ajaran FROM semester ORDER BY id_semester ASC");
                $semesterStmt->execute();
                $semesters = $semesterStmt->fetchAll(PDO::FETCH_ASSOC);

                // default semester = 1 (atau bisa dari jadwal aktif kalau ada)
                $selectedSemester = $jadwalNow ? (int)$jadwalNow[0]['id_semester'] : 1;
                ?>

                <!-- Modal Edit Jadwal (HANYA SEKALI) -->
                <div id="modalEditJadwal"
                  class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                  role="dialog" aria-modal="true"
                  onclick="overlayClose(event, 'modalEditJadwal')">

                  <div class="bg-white rounded-2xl p-8 w-full max-w-6xl shadow-2xl relative animate-fade-in
            max-h-[90vh] overflow-y-auto"
                    onclick="event.stopPropagation()">

                    <button onclick="closeModal('modalEditJadwal')"
                      class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                    <h2 class="text-2xl font-semibold mb-6 text-gray-800">✏️ Edit Jadwal Perkuliahan</h2>

                    <form method="POST" id="formEditJadwal">
                      <div class="grid grid-cols-2 gap-6">

                        <!-- PANEL KIRI -->
                        <div>
                          <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold">Pilih Mata Kuliah</h3>
                            <select id="semesterSelect" name="semester"
                              class="w-90 border border-gray-300 rounded-lg p-3 mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              onchange="loadMatkulBySemester(this.value)">
                              <?php foreach ($semesters as $s): ?>
                                <option value="<?= $s['id_semester'] ?>" <?= $s['id_semester'] == $selectedSemester ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($s['nama_semester']) ?> (<?= htmlspecialchars($s['tahun_ajaran']) ?>)
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <!-- Kontainer Scroll Matkul -->
                          <div id="matkulList"
                            style="max-height: 320px; overflow-y: auto; overflow-x: hidden; padding-right: 6px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.3) transparent;"
                            class="space-y-2 border rounded-lg p-3">
                            <!-- Diisi via AJAX -->
                          </div>
                        </div>

                        <!-- PANEL KANAN -->
                        <div>
                          <h3 class="text-lg font-semibold mb-3">Jadwal Saat Ini</h3>
                          <div id="selectedMatkul"
                            style="max-height: 320px; overflow-y: auto; overflow-x: hidden; padding-right: 6px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.3) transparent;"
                            class="border rounded-lg p-3 bg-gray-50">
                            <?php if ($jadwalNow): ?>
                              <?php foreach ($jadwalNow as $j): ?>
                                <div id="selected-<?= (int)$j['id_matkul'] ?>"
                                  class="flex justify-between items-center bg-white border p-2 rounded mb-2">
                                  <div>
                                    <p class="font-medium"><?= htmlspecialchars($j['nama_matkul']) ?></p>
                                    <p class="text-xs text-gray-500">
                                      <?= htmlspecialchars($j['hari']) ?>,
                                      <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                                      (<?= htmlspecialchars($j['ruangan']) ?>)
                                    </p>
                                  </div>
                                  <button type="button" class="text-red-500 text-sm hover:text-red-700"
                                    onclick="removeFromRight(<?= (int)$j['id_matkul'] ?>)">
                                    ✕
                                  </button>
                                  <input type="hidden" name="id_matkul[]" value="<?= (int)$j['id_matkul'] ?>">
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <p class="text-gray-500">Belum ada jadwal tersimpan</p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>

                      <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeModal('modalEditJadwal')"
                          class="px-5 py-2.5 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700 font-medium transition">
                          Batal
                        </button>
                        <button type="submit" name="simpan_edit"
                          class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-slate-600 hover:text-white font-semibold shadow transition">
                          Simpan Perubahan
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <script>
                  // --- seed daftar terpilih dari PHP (jadwal aktif) ---
                  const selectedIds = new Set(<?= json_encode($selectedMatkulIds, JSON_NUMERIC_CHECK) ?>);

                  // buka modal + load kiri (default semester di select)
                  function openEditModal() {
                    openModal('modalEditJadwal');
                    const sem = document.getElementById('semesterSelect').value;
                    loadMatkulBySemester(sem); // isi awal
                  }

                  // load checkbox matkul by semester (AJAX)
                  function loadMatkulBySemester(semesterId) {
                    const selected = Array.from(selectedIds).join(',');
                    fetch('get_matkul.php?semester=' + semesterId + '&selected=' + selected)
                      .then(res => res.text())
                      .then(html => {
                        document.getElementById('matkulList').innerHTML = html;

                        // re-attach listener untuk checkbox baru
                        document.querySelectorAll('#matkulList input[type="checkbox"]').forEach(cb => {
                          cb.addEventListener('change', () => onLeftCheckboxChange(cb));
                        });
                      })
                      .catch(err => console.error(err));
                  }

                  // klik checkbox kiri -> sinkron ke kanan
                  function onLeftCheckboxChange(cb) {
                    const id = parseInt(cb.dataset.id, 10);
                    const nama = cb.dataset.nama;
                    const hari = cb.dataset.hari || '';
                    const jm = cb.dataset.jm || '';
                    const js = cb.dataset.js || '';
                    const ruang = cb.dataset.ruang || '';

                    if (cb.checked) {
                      if (selectedIds.has(id)) return; // sudah ada
                      addToRight({
                        id,
                        nama,
                        hari,
                        jm,
                        js,
                        ruang
                      });
                    } else {
                      removeFromRight(id);
                    }
                  }

                  // tambah 1 item ke panel kanan
                  function addToRight({
                    id,
                    nama,
                    hari,
                    jm,
                    js,
                    ruang
                  }) {
                    selectedIds.add(id);
                    const container = document.getElementById('selectedMatkul');
                    const node = document.createElement('div');
                    node.id = 'selected-' + id;
                    node.className = 'flex justify-between items-center bg-white border p-2 rounded mb-2';
                    node.innerHTML = `
      <div>
        <p class="font-medium">${nama}</p>
        <p class="text-xs text-gray-500">${hari ? `${hari}, ${jm?.slice(0,5)} - ${js?.slice(0,5)} (${ruang})` : ''}</p>
      </div>
      <button type="button" class="text-red-500 text-sm hover:text-red-700" onclick="removeFromRight(${id})">✕</button>
      <input type="hidden" name="id_matkul[]" value="${id}">
    `;
                    container.appendChild(node);
                  }

                  // hapus 1 item dari panel kanan + uncheck kiri kalau ada
                  function removeFromRight(id) {
                    selectedIds.delete(id);
                    const node = document.getElementById('selected-' + id);
                    if (node) node.remove();
                    const box = document.querySelector(`#matkulList input[type="checkbox"][data-id="${id}"]`);
                    if (box) box.checked = false;
                  }
                </script>

                <!-- Notifikasi -->
                <div id="notif"
                  class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition duration-500 z-[9999]">
                </div>

                <script>
                  function showNotif(message) {
                    const notif = document.getElementById('notif');
                    notif.textContent = message;
                    notif.classList.remove('opacity-0');
                    notif.classList.add('opacity-100');

                    setTimeout(() => {
                      notif.classList.remove('opacity-100');
                      notif.classList.add('opacity-0');
                    }, 3000); // hilang setelah 3 detik
                  }
                </script>

                <script>
                  function openModal(id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.classList.remove('hidden');
                    el.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    const first = el.querySelector('input, textarea, select, button');
                    if (first) first.focus();
                  }

                  function closeModal(id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.classList.add('hidden');
                    el.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                  }

                  function overlayClose(e, id) {
                    if (e.target.id === id) closeModal(id);
                  }

                  document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                      document.querySelectorAll('[role="dialog"]').forEach(el => {
                        if (!el.classList.contains('hidden')) el.classList.add('hidden');
                      });
                      document.body.style.overflow = '';
                    }
                  });
                </script>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Jadwal Diganti -->
      <!-- <?php
            include './config/db.php'; // koneksi

            // Ambil data matkul + semester
            $stmt = $conn->prepare("
    SELECT j.id_jadwal,
           mk.id_matkul, mk.nama_matkul, mk.hari, mk.jam_mulai, mk.jam_selesai, mk.ruangan,
           s.nama_semester, s.tahun_ajaran, p.status
    FROM jadwal j
    INNER JOIN presensi p ON j.id_jadwal = p.id_jadwal AND p.status = 'Diganti'
    INNER JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    ORDER BY FIELD(mk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
             mk.jam_mulai
");
            $stmt->execute();
            $matkul = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

      <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
              <h6>Daftar Mata Kuliah</h6>
            </div>
            <div class="flex-auto px-0 pt-0 pb-2">
              <div class="p-0 overflow-x-auto">
                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                  <thead class="align-bottom">
                    <tr>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Mata Kuliah</th>
                      <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Semester</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Hari</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Jam</th>
                      <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Ruangan</th>
                  </thead>
                  <tbody>
                    <?php foreach ($matkul as $row): ?>
                      <tr class="bg-white">
                        <td class="p-2 align-middle border-b whitespace-nowrap"></td>
                        <td class="p-2 align-middle border-b whitespace-nowrap">
                          <h6 class="mb-0 text-sm leading-normal"><?= $row['nama_matkul'] ?></h6>
                        </td>
                        <td class="p-2 align-middle border-b whitespace-nowrap">
                          <p class="mb-0 text-xs font-semibold"><?= $row['nama_semester'] ?></p>
                          <p class="mb-0 text-xs text-slate-400"><?= $row['tahun_ajaran'] ?></p>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="px-2.5 py-1 inline-block text-xs font-bold uppercase rounded-lg bg-gray-200 text-slate-600">
                            <?= $row['hari'] ?>
                          </span>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="text-xs font-semibold text-slate-400">
                            <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                          </span>
                        </td>
                        <td class="p-2 text-center align-middle border-b whitespace-nowrap">
                          <span class="text-xs font-semibold text-slate-400"><?= $row['ruangan'] ?></span>
                        </td>
                      </tr>

                      simpan data lengkap untuk modal
                      <?php $modals[] = [
                        'id_jadwal' => $row['id_jadwal'],
                        'id_matkul' => $row['id_matkul']
                      ]; ?>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div> -->

      <!-- footer  -->
      <?php include '../components/footer.php'; ?>
    </div>
  </main>
</body>
<!-- plugin for scrollbar  -->
<script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
<!-- github button -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- main script file  -->
<script src="../assets/js/soft-ui-dashboard-tailwind.js?v=1.0.5" async></script>

</html>