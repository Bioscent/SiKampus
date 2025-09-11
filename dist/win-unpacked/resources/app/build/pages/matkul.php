<?php
include '../config/db.php'; // koneksi

// PROSES TAMBAH MATA KULIAH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_matkul'])) {
  $id_semester   = (int) $_POST['id_semester'];
  $nama_matkul   = trim($_POST['nama_matkul']);
  $nama_pengampu = trim($_POST['nama_pengampu']);
  $hari          = $_POST['hari'];
  $jam_mulai     = $_POST['jam_mulai'];
  $jam_selesai   = $_POST['jam_selesai'];
  $ruangan       = trim($_POST['ruangan']);

  try {
    $stmt = $conn->prepare("INSERT INTO mata_kuliah 
      (id_semester, nama_matkul, nama_pengampu, hari, jam_mulai, jam_selesai, ruangan) 
      VALUES (:id_semester, :nama_matkul, :nama_pengampu, :hari, :jam_mulai, :jam_selesai, :ruangan)");
    $stmt->execute([
      ':id_semester'   => $id_semester,
      ':nama_matkul'   => $nama_matkul,
      ':nama_pengampu' => $nama_pengampu,
      ':hari'          => $hari,
      ':jam_mulai'     => $jam_mulai,
      ':jam_selesai'   => $jam_selesai,
      ':ruangan'       => $ruangan
    ]);

    // Notif sukses
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Mata kuliah berhasil ditambahkan!');
        setTimeout(() => { window.location.href = window.location.href; }, 2000);
      });
    </script>";
  } catch (PDOException $e) {
    $msg = htmlspecialchars($e->getMessage());
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Gagal menambah mata kuliah: {$msg}');
        setTimeout(() => { window.location.href = window.location.href; }, 3000);
      });
    </script>";
  }
}

// PROSES EDIT MATA KULIAH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_matkul'])) {
  $id_matkul     = (int) $_POST['edit_matkul'];
  $id_semester   = (int) $_POST['id_semester'];
  $nama_matkul   = trim($_POST['nama_matkul']);
  $nama_pengampu = trim($_POST['nama_pengampu']);
  $hari          = $_POST['hari'];
  $jam_mulai     = $_POST['jam_mulai'];
  $jam_selesai   = $_POST['jam_selesai'];
  $ruangan       = trim($_POST['ruangan']);

  try {
    $stmt = $conn->prepare("UPDATE mata_kuliah 
      SET id_semester = :id_semester,
          nama_matkul = :nama_matkul,
          nama_pengampu = :nama_pengampu,
          hari = :hari,
          jam_mulai = :jam_mulai,
          jam_selesai = :jam_selesai,
          ruangan = :ruangan
      WHERE id_matkul = :id");
    $stmt->execute([
      ':id'            => $id_matkul,
      ':id_semester'   => $id_semester,
      ':nama_matkul'   => $nama_matkul,
      ':nama_pengampu' => $nama_pengampu,
      ':hari'          => $hari,
      ':jam_mulai'     => $jam_mulai,
      ':jam_selesai'   => $jam_selesai,
      ':ruangan'       => $ruangan
    ]);

    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Mata kuliah berhasil diperbarui!');
        setTimeout(() => { window.location.href = window.location.href; }, 2000);
      });
    </script>";
  } catch (PDOException $e) {
    $msg = htmlspecialchars($e->getMessage());
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Gagal memperbarui mata kuliah: {$msg}');
        setTimeout(() => { window.location.href = window.location.href; }, 3000);
      });
    </script>";
  }
}

// PROSES HAPUS MATA KULIAH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_matkul'])) {
  $id_matkul = (int) $_POST['hapus_matkul'];

  try {
    $stmt = $conn->prepare("DELETE FROM mata_kuliah WHERE id_matkul = :id");
    $stmt->execute([':id' => $id_matkul]);

    // Notif sukses
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Mata kuliah berhasil dihapus!');
        setTimeout(() => { window.location.href = window.location.href; }, 2000);
      });
    </script>";
  } catch (PDOException $e) {
    // Notif gagal
    $msg = htmlspecialchars($e->getMessage());
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotif('Gagal menghapus mata kuliah: {$msg}');
        setTimeout(() => { window.location.href = window.location.href; }, 3000);
      });
    </script>";
  }
}

// Default semester = 1 kalau tidak ada pilihan
$selectedSemester = isset($_GET['semester']) ? (int) $_GET['semester'] : 1;

// Ambil mata kuliah sesuai semester
$stmt = $conn->prepare("
    SELECT mk.id_matkul, mk.nama_matkul, mk.nama_pengampu, mk.hari, 
           mk.jam_mulai, mk.jam_selesai, mk.ruangan, s.nama_semester, s.tahun_ajaran, s.id_semester
    FROM mata_kuliah mk
    LEFT JOIN semester s ON mk.id_semester = s.id_semester
    WHERE mk.id_semester = :id_semester
    ORDER BY mk.hari, mk.jam_mulai ASC
");
$stmt->execute(['id_semester' => $selectedSemester]);
$matkul = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data semester
$semesterStmt = $conn->prepare("SELECT id_semester, nama_semester, tahun_ajaran FROM semester ORDER BY id_semester ASC");
$semesterStmt->execute();
$semesters = $semesterStmt->fetchAll(PDO::FETCH_ASSOC);
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

  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>


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
  $activePage = "matkul";
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
          <h6 class="mb-0 font-bold capitalize">Mata Kuliah</h6>
        </nav>
      </div>
    </nav>

    <!-- end Navbar -->


    <div class="w-full px-6 py-6 mx-auto">
      <!-- table 1 -->
      <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-soft-xl rounded-2xl">
            <!-- Header + Tombol Tambah -->
            <div class="flex items-center justify-between p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
              <h6 class="text-lg font-semibold text-slate-700">Daftar Mata Kuliah</h6>

              <div class="flex items-center gap-4">
                <!-- Dropdown Semester -->
                <form method="GET">
                  <label for="semester" class="mr-2 font-medium text-slate-600">Semester:</label>
                  <select name="semester" id="semester" onchange="this.form.submit()"
                    class="px-3 py-2 rounded-xl border border-gray-300 shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-200">
                    <?php foreach ($semesters as $s): ?>
                      <option value="<?= $s['id_semester'] ?>"
                        <?= $s['id_semester'] == $selectedSemester ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nama_semester']) ?> (<?= htmlspecialchars($s['tahun_ajaran']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>

                <!-- Tombol Tambah -->
                <button onclick="openModal('modalTambahMatkul')"
                  class="px-4 py-2 font-semibold text-white rounded-xl shadow-md text-sm bg-gradient-to-r from-green-500 to-lime-400 hover:scale-105 hover:shadow-lg transition duration-200">
                  + Tambah Mata Kuliah
                </button>
              </div>
            </div>

            <!-- Tabel -->
            <div class="flex-auto px-0 pt-0 pb-2">
              <div class="p-0 overflow-x-auto">
                <table class="items-center w-full mb-0 text-slate-500 border-gray-200">
                  <thead>
                    <tr>
                      <th class="px-4 py-2 text-left">Mata Kuliah</th>
                      <th class="px-4 py-2 text-left">Semester</th>
                      <th class="px-4 py-2 text-center">Hari</th>
                      <th class="px-4 py-2 text-center">Jam</th>
                      <th class="px-4 py-2 text-center">Ruangan</th>
                      <th class="px-4 py-2 text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($matkul)): ?>
                      <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                          Belum ada mata kuliah
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($matkul as $row): ?>
                        <tr class="border-b">
                          <td class="px-4 py-2"><?= htmlspecialchars($row['nama_matkul']) ?></td>
                          <td class="px-4 py-2">
                            <p class="mb-0 text-xs font-semibold"><?= htmlspecialchars($row['nama_semester']) ?></p>
                            <p class="mb-0 text-xs text-slate-400"><?= htmlspecialchars($row['tahun_ajaran']) ?></p>
                          </td>
                          <td class="px-4 py-2 text-center"><?= htmlspecialchars($row['hari']) ?></td>
                          <td class="px-4 py-2 text-center">
                            <?= date("H:i", strtotime($row['jam_mulai'])) ?> - <?= date("H:i", strtotime($row['jam_selesai'])) ?>
                          </td>
                          <td class="px-4 py-2 text-center"><?= htmlspecialchars($row['ruangan']) ?></td>
                          <td class="p-2 align-middle whitespace-nowrap">
                            <div class="flex gap-2 justify-center">
                              <!-- Tombol Detail -->
                              <button onclick="openModal('modalDetail<?= $row['id_matkul'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-blue-600 to-cyan-400 hover:scale-105">
                                <i class="fas fa-eye"></i>
                              </button>
                              <!-- Tombol Edit -->
                              <button onclick="openModal('modalEdit<?= $row['id_matkul'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-yellow-500 to-rose-400 hover:scale-105">
                                <i class="fas fa-edit"></i>
                              </button>
                              <!-- Tombol Hapus -->
                              <button onclick="openModal('modalHapus<?= $row['id_matkul'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-red-500 to-pink-500 hover:scale-105">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>

                <?php foreach ($matkul as $row): ?>
                  <!-- Modal Hapus -->
                  <div id="modalHapus<?= $row['id_matkul'] ?>"
                    class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                    role="dialog" aria-modal="true"
                    onclick="overlayClose(event, 'modalHapus<?= $row['id_matkul'] ?>')">

                    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl relative animate-fade-in"
                      onclick="event.stopPropagation()">

                      <!-- Tombol Close -->
                      <button onclick="closeModal('modalHapus<?= $row['id_matkul'] ?>')"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                      <h2 class="text-xl font-semibold mb-6 text-gray-800">⚠️ Konfirmasi Hapus</h2>

                      <p class="mb-6 text-gray-600">Apakah kamu yakin ingin menghapus mata kuliah <b><?= htmlspecialchars($row['nama_matkul']) ?></b>?</p>

                      <form method="POST" class="flex justify-end gap-3">
                        <input type="hidden" name="hapus_matkul" value="<?= $row['id_matkul'] ?>">
                        <button type="button" onclick="closeModal('modalHapus<?= $row['id_matkul'] ?>')"
                          class="px-4 py-2 font-bold text-gray-700 bg-gray-200 rounded-lg shadow-soft-md text-xs hover:scale-105">
                          Batal
                        </button>
                        <button type="submit"
                          class="px-4 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-red-600 to-pink-500 hover:scale-105">
                          Hapus
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>

                <!-- Modal Edit -->
                <?php foreach ($matkul as $row): ?>
                  <div id="modalEdit<?= $row['id_matkul'] ?>"
                    class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                    role="dialog" aria-modal="true"
                    onclick="overlayClose(event, 'modalEdit<?= $row['id_matkul'] ?>')">

                    <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in
                max-h-[90vh] overflow-y-auto"
                      onclick="event.stopPropagation()">

                      <button onclick="closeModal('modalEdit<?= $row['id_matkul'] ?>')"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                      <h2 class="text-2xl font-semibold mb-6 text-gray-800">✏️ Edit Mata Kuliah</h2>

                      <form method="POST" class="space-y-4">
                        <input type="hidden" name="edit_matkul" value="<?= $row['id_matkul'] ?>">

                        <!-- Nama Matkul -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                          <input type="text" name="nama_matkul" value="<?= htmlspecialchars($row['nama_matkul']) ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                        </div>

                        <!-- Nama Pengampu -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Nama Pengampu</label>
                          <input type="text" name="nama_pengampu" value="<?= htmlspecialchars($row['nama_pengampu']) ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                        </div>

                        <!-- Hari -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Hari</label>
                          <select name="hari" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                            <?php
                            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                            foreach ($hariList as $hari) {
                              $selected = $row['hari'] === $hari ? "selected" : "";
                              echo "<option $selected>$hari</option>";
                            }
                            ?>
                          </select>
                        </div>

                        <!-- Jam Mulai & Selesai -->
                        <div class="grid grid-cols-2 gap-4">
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                            <input type="time" name="jam_mulai" value="<?= $row['jam_mulai'] ?>"
                              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                            <input type="time" name="jam_selesai" value="<?= $row['jam_selesai'] ?>"
                              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                          </div>
                        </div>

                        <!-- Ruangan -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Ruangan</label>
                          <input type="text" name="ruangan" value="<?= htmlspecialchars($row['ruangan']) ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                        </div>

                        <!-- Semester -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Semester</label>
                          <select name="id_semester" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400" required>
                            <?php foreach ($semesters as $s): ?>
                              <option value="<?= $s['id_semester'] ?>"
                                <?= $row['id_semester'] == $s['id_semester'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_semester']) ?> (<?= htmlspecialchars($s['tahun_ajaran']) ?>)
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex justify-end">
                          <button type="submit"
                            class="px-4 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-yellow-500 to-rose-400 hover:scale-105">
                            Simpan Perubahan
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>

                <?php foreach ($matkul as $row): ?>
                  <!-- Modal Detail -->
                  <div id="modalDetail<?= $row['id_matkul'] ?>"
                    class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                    role="dialog" aria-modal="true"
                    onclick="overlayClose(event, 'modalDetail<?= $row['id_matkul'] ?>')">

                    <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in
                max-h-[90vh] overflow-y-auto"
                      onclick="event.stopPropagation()">

                      <!-- Tombol Close -->
                      <button onclick="closeModal('modalDetail<?= $row['id_matkul'] ?>')"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                      <h2 class="text-2xl font-semibold mb-6 text-gray-800">📘 Detail Mata Kuliah</h2>

                      <form class="space-y-4">
                        <!-- Nama Matkul -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                          <input type="text" value="<?= htmlspecialchars($row['nama_matkul']) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>

                        <!-- Nama Pengampu -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Nama Pengampu</label>
                          <input type="text" value="<?= htmlspecialchars($row['nama_pengampu']) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>

                        <!-- Hari -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Hari</label>
                          <input type="text" value="<?= htmlspecialchars($row['hari']) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>

                        <!-- Jam -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Jam</label>
                          <input type="text" value="<?= date("H:i", strtotime($row['jam_mulai'])) ?> - <?= date("H:i", strtotime($row['jam_selesai'])) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>

                        <!-- Ruangan -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Ruangan</label>
                          <input type="text" value="<?= htmlspecialchars($row['ruangan']) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>

                        <!-- Semester -->
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Semester</label>
                          <input type="text" value="<?= htmlspecialchars($row['nama_semester']) ?>"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>


                <!-- Modal Tambah Mata Kuliah -->
                <div id="modalTambahMatkul"
                  class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                  role="dialog" aria-modal="true"
                  onclick="overlayClose(event, 'modalTambahMatkul')">

                  <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in
              max-h-[90vh] overflow-y-auto"
                    onclick="event.stopPropagation()">

                    <!-- Tombol Close -->
                    <button onclick="closeModal('modalTambahMatkul')"
                      class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                    <h2 class="text-2xl font-semibold mb-6 text-gray-800">➕ Tambah Mata Kuliah</h2>

                    <form method="POST" class="space-y-4">

                      <!-- Nama Matkul -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                        <input type="text" name="nama_matkul"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                      </div>

                      <!-- Nama Pengampu -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Pengampu</label>
                        <input type="text" name="nama_pengampu"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                      </div>

                      <!-- Hari -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Hari</label>
                        <select name="hari" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                          <option value="">-- Pilih Hari --</option>
                          <option>Senin</option>
                          <option>Selasa</option>
                          <option>Rabu</option>
                          <option>Kamis</option>
                          <option>Jumat</option>
                          <option>Sabtu</option>
                          <option>Minggu</option>
                        </select>
                      </div>

                      <!-- Jam Mulai & Selesai -->
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                          <input type="time" name="jam_mulai"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                          <input type="time" name="jam_selesai"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                        </div>
                      </div>

                      <!-- Ruangan -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Ruangan</label>
                        <input type="text" name="ruangan"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                      </div>

                      <!-- Semester -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="id_semester"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>
                          <option value="">-- Pilih Semester --</option>
                          <?php foreach ($semesters as $s): ?>
                            <option value="<?= $s['id_semester'] ?>">
                              <?= htmlspecialchars($s['nama_semester']) ?> (<?= htmlspecialchars($s['tahun_ajaran']) ?>)
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <!-- Tombol Submit -->
                      <div class="flex justify-end">
                        <button type="submit" name="tambah_matkul"
                          class="px-4 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-green-600 to-lime-400 hover:scale-105">
                          Simpan
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Notifikasi -->
                <div id="notif"
                  class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition duration-500 z-50">
                </div>

                <script>
                  function openModal(id) {
                    document.getElementById(id).classList.remove("hidden");
                  }

                  function closeModal(id) {
                    document.getElementById(id).classList.add("hidden");
                  }

                  function overlayClose(event, id) {
                    if (event.target.id === id) {
                      closeModal(id);
                    }
                  }
                </script>

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
              </div>
            </div>
          </div>
        </div>
      </div>

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