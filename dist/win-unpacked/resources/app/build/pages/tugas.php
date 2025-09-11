<?php
include '../config/db.php'; // pastikan pakai PDO $conn

// PROSES SUBMIT JAWABAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_jawaban'])) {
  $id_tugas = (int) $_POST['id_tugas'];
  $uploaded_files = [];

  if (!empty($_FILES['jawaban']['name'][0])) {
    // pakai path relatif
    $upload_dir = __DIR__ . "/uploads/jawaban/";
    $public_dir = "uploads/jawaban/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    foreach ($_FILES['jawaban']['name'] as $key => $originalName) {
      $tmp_name = $_FILES['jawaban']['tmp_name'][$key] ?? null;
      $error = $_FILES['jawaban']['error'][$key] ?? UPLOAD_ERR_NO_FILE;

      if ($tmp_name && $error === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $new_name = time() . "_" . bin2hex(random_bytes(6)) . ($ext ? "." . $ext : "");
        $target_path = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
          $uploaded_files[] = $new_name;
        }
      }
    }
  }

  if (!empty($uploaded_files)) {
    $json_files = json_encode($uploaded_files);

    try {
      $stmt = $conn->prepare("
        UPDATE tugas 
        SET foto_submit = :foto_submit, status = 'Selesai' 
        WHERE id_tugas = :id_tugas
      ");
      $stmt->execute([
        ':foto_submit' => $json_files,
        ':id_tugas' => $id_tugas
      ]);

      // Notifikasi sukses
      echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotif('Jawaban berhasil dikirim!');
            setTimeout(() => {
                window.location.href = window.location.href;
            }, 2000);
        });
        </script>";
    } catch (PDOException $e) {
      $msg = addslashes($e->getMessage());
      echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotif('Error DB: {$msg}');
        });
        </script>";
    }
  } else {
    // Tidak ada file yang diupload
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotif('Tidak ada file yang berhasil di-upload');
    });
    </script>";
  }
}

// PROSES HAPUS TUGAS
if (isset($_POST['hapus_tugas'])) {
  $id_tugas = (int) $_POST['hapus_tugas'];
  $stmt = $conn->prepare("DELETE FROM tugas WHERE id_tugas = :id_tugas");
  $stmt->execute([':id_tugas' => $id_tugas]);

  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      showNotif('Tugas berhasil dihapus!');
      setTimeout(() => { window.location.href = window.location.href }, 1500);
    });
  </script>";
}

// PROSES RESET TUGAS
if (isset($_POST['reset_tugas'])) {
  $id_tugas = (int) $_POST['reset_tugas'];

  $stmt = $conn->prepare("
    UPDATE tugas 
    SET foto_submit = NULL, status = 'Belum dikerjakan' 
    WHERE id_tugas = :id_tugas
  ");
  $stmt->execute([':id_tugas' => $id_tugas]);

  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      showNotif('Tugas berhasil direset!');
      setTimeout(() => { window.location.href = window.location.href }, 1500);
    });
  </script>";
}
?>

<?php
// Pastikan kedua variabel GET ada
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$pega = isset($_GET['pega']) && is_numeric($_GET['pega']) ? (int)$_GET['pega'] : 1;
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
  $activePage = "tugas";
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
          <h6 class="mb-0 font-bold capitalize">Tugas</h6>
        </nav>
      </div>
    </nav>

    <!-- end Navbar -->


    <div class="w-full px-6 py-6 mx-auto">
      <!-- table 1 -->

      <?php

      // Tentukan jumlah data per halaman
      $limit = 5;

      // Ambil halaman saat ini dari URL, default 1
      $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

      // Hitung offset
      $offset = ($page - 1) * $limit;

      // Ambil total data untuk menghitung total halaman
      $totalStmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    WHERE t.status != 'Selesai'
");
      $totalStmt->execute();
      $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
      $totalData = $totalRow['total'];
      $totalPages = ceil($totalData / $limit);

      // Ambil data tugas sesuai halaman
      $stmt = $conn->prepare("
    SELECT t.id_tugas, t.id_matkul, t.judul, t.deskripsi, t.deadline, 
           t.foto_opsional, t.foto_submit, t.status, t.created_at,
           mk.nama_matkul, mk.nama_pengampu,
           s.nama_semester, s.tahun_ajaran
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    WHERE t.status != 'Selesai'
    ORDER BY t.deadline ASC
    LIMIT :limit OFFSET :offset
");
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();
      $tugas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
              <h6>Daftar Tugas</h6>
            </div>
            <div class="flex-auto px-0 pt-0 pb-2">
              <div class="p-0 overflow-x-auto">
                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                  <thead>
                    <tr>
                      <th class="px-4 py-2 text-left">Mata Kuliah</th>
                      <th class="px-4 py-2 text-left">Judul</th>
                      <th class="px-4 py-2 text-left">Deskripsi</th>
                      <th class="px-4 py-2 text-center">Deadline</th>
                      <th class="px-4 py-2 text-center">Status</th>
                      <th class="px-4 py-2 text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($tugas)): ?>
                      <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                          Belum ada tugas
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($tugas as $row): ?>
                        <tr class="border-b">
                          <td class="px-4 py-2"><?= htmlspecialchars($row['nama_matkul']) ?></td>
                          <td class="px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
                          <td class="px-4 py-2">
                            <?php
                            $raw = trim(strip_tags($row['deskripsi'] ?? ''));
                            $limit = 3; // jumlah kata maksimal yang ditampilkan

                            if ($raw === '') {
                              echo '-';
                            } else {
                              $words = preg_split('/\s+/u', $raw);
                              if (count($words) <= $limit) {
                                echo nl2br(htmlspecialchars($raw, ENT_QUOTES));
                              } else {
                                $short = implode(' ', array_slice($words, 0, $limit));
                                echo '<span title="' . htmlspecialchars($raw, ENT_QUOTES) . '">' . htmlspecialchars($short, ENT_QUOTES) . '...</span>';
                              }
                            }
                            ?>
                          </td>
                          <td class="px-4 py-2 text-center">
                            <?= $row['deadline'] ? date("d-m-Y H:i", strtotime($row['deadline'])) : '-' ?>
                          </td>
                          <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold
                              <?= $row['status'] == 'Selesai' ? 'bg-green-200 text-green-700' : ($row['status'] == 'Sedang Dikerjakan' ? 'bg-yellow-200 text-yellow-700' : 'bg-red-200 text-red-700') ?>">
                              <?= $row['status'] ?>
                            </span>
                          </td>
                          <td class="p-2 align-middle whitespace-nowrap">
                            <div class="flex gap-2">
                              <!-- Tombol Detail -->
                              <button onclick="openModal('modalDetail<?= $row['id_tugas'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-blue-600 to-cyan-400 hover:scale-105">
                                <i class="fas fa-eye"></i>
                              </button>

                              <!-- Tombol Kerjakan (hanya tampil kalau belum selesai) -->
                              <?php if ($row['status'] !== 'Selesai'): ?>
                                <button onclick="openModal('modalKerjakan<?= $row['id_tugas'] ?>')"
                                  class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-green-600 to-lime-400 hover:scale-105">
                                  <i class="fas fa-pencil-alt"></i> Kerjakan
                                </button>
                              <?php endif; ?>

                              <!-- Tombol Hapus -->
                              <button onclick="openModal('modalHapus<?= $row['id_tugas'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-red-600 to-pink-500 hover:scale-105">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </td>

                          <!-- simpan data modal -->
                          <?php $modalTugas[] = $row; ?>
                        </tr>
                        <!-- Modal Hapus Tugas -->
                        <div id="modalHapus<?= $row['id_tugas'] ?>"
                          class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                          role="dialog" aria-modal="true"
                          onclick="overlayClose(event, 'modalHapus<?= $row['id_tugas'] ?>')">

                          <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl relative animate-fade-in"
                            onclick="event.stopPropagation()">

                            <!-- Tombol Close -->
                            <button onclick="closeModal('modalHapus<?= $row['id_tugas'] ?>')"
                              class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                            <h2 class="text-xl font-semibold mb-6 text-gray-800">⚠️ Konfirmasi Hapus</h2>

                            <p class="mb-6 text-gray-600">
                              Apakah kamu yakin ingin menghapus tugas <b><?= htmlspecialchars($row['judul']) ?></b>?
                            </p>

                            <form method="POST" class="flex justify-end space-x-2">
                              <input type="hidden" name="hapus_tugas" value="<?= $row['id_tugas'] ?>">
                              <button type="button" onclick="closeModal('modalHapus<?= $row['id_tugas'] ?>')"
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
                    <?php endif; ?>
                  </tbody>
                </table>

                <!-- Pagination Tugas Belum Selesai -->
                <div class="mt-4 flex justify-center space-x-2">
                  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&pega=<?= $pega ?>"
                      class="px-3 py-1 rounded border <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' ?>">
                      <?= $i ?>
                    </a>
                  <?php endfor; ?>
                </div>

                <?php if (!empty($modalTugas)): ?>
                  <?php foreach ($modalTugas as $m): ?>
                    <!-- Modal Detail -->
                    <div id="modalDetail<?= $m['id_tugas'] ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalDetail<?= $m['id_tugas'] ?>')">

                      <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in
              max-h-[90vh] overflow-y-auto"
                        onclick="event.stopPropagation()">

                        <!-- Tombol Close -->
                        <button onclick="closeModal('modalDetail<?= $m['id_tugas'] ?>')"
                          class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📌 Detail Tugas</h2>

                        <form class="space-y-4">
                          <!-- Mata Kuliah -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                            <input type="text" value="<?= htmlspecialchars($m['nama_matkul']) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Judul -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Judul</label>
                            <input type="text" value="<?= htmlspecialchars($m['judul']) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Deskripsi -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea class="w-full border rounded-lg px-3 py-2 bg-gray-100" rows="4" readonly><?= htmlspecialchars($m['deskripsi']) ?></textarea>
                          </div>

                          <!-- Deadline -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Deadline</label>
                            <input type="text" value="<?= date("d-m-Y H:i", strtotime($m['deadline'])) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Foto Opsional -->
                          <?php if (!empty($m['foto_opsional'])): ?>
                            <?php
                            $fotos = json_decode($m['foto_opsional'], true);
                            if ($fotos):
                            ?>
                              <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Tugas</label>

                                <div class="foto-scroll-container"
                                  style="width: 100%; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding-bottom: 6px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.3) transparent;">

                                  <?php foreach ($fotos as $i => $file): ?>
                                    <?php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $filePath = htmlspecialchars($file); // ✅ pakai langsung
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>

                                    <?php if ($isImage): ?>
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-block align-top mr-3"
                                        style="width: 220px; height: 140px;">
                                        <img src="<?= $filePath ?>"
                                          alt="Foto Tugas"
                                          class="w-full h-full object-cover rounded-lg border transition duration-300 group-hover:brightness-75" />
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-lg"
                                          style="background-color: rgba(0, 0, 0, 0.3);">
                                          <i class="fas fa-eye text-white text-3xl"></i>
                                        </div>
                                      </a>
                                    <?php else: ?>
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-flex flex-col items-center justify-center mr-3 bg-gray-100 border rounded-lg"
                                        style="width: 220px; height: 140px;">
                                        <?php if ($ext === 'pdf'): ?>
                                          <i class="fas fa-file-pdf text-red-500 text-6xl"></i>
                                        <?php else: ?>
                                          <i class="fas fa-file-alt text-blue-500 text-6xl"></i>
                                        <?php endif; ?>
                                        <span class="mt-2 text-sm text-gray-700 uppercase"><?= strtoupper($ext) ?> File</span>
                                      </a>
                                    <?php endif; ?>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php endif; ?>
                        </form>
                      </div>
                    </div>

                    <!-- Modal Kerjakan -->
                    <div id="modalKerjakan<?= $m['id_tugas'] ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalKerjakan<?= $m['id_tugas'] ?>')">
                      <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in"
                        onclick="event.stopPropagation()">
                        <button onclick="closeModal('modalKerjakan<?= $m['id_tugas'] ?>')"
                          class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">✍️ Kerjakan Tugas</h2>
                        <form method="POST" enctype="multipart/form-data">
                          <input type="hidden" name="id_tugas" value="<?= $m['id_tugas'] ?>">
                          <label class="block mb-2 text-sm font-medium text-gray-700">Upload Jawaban (boleh banyak)</label>
                          <input type="file" name="jawaban[]" multiple required
                            class="w-full border border-gray-300 rounded-lg p-2 mb-6 text-sm text-gray-700">

                          <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeModal('modalKerjakan<?= $m['id_tugas'] ?>')"
                              class="px-5 py-2.5 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700 hover:text-white font-medium transition">
                              Batal
                            </button>
                            <button type="submit" name="submit_jawaban"
                              class="px-5 py-2.5 bg-green-600 hover:bg-green-700 rounded-lg text-slate-600 hover:text-white font-semibold shadow transition">
                              Kirim
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <!-- Fullscreen Image Modal -->
                <!-- <div id="imageModal" class="fixed inset-0 hidden bg-black bg-opacity-90 z-50 flex items-center justify-center">
                  <span class="absolute top-5 right-7 text-white text-4xl cursor-pointer" onclick="closeImageModal()">&times;</span>
                  <img id="imageModalContent" class="max-h-[90%] max-w-[90%] rounded-lg shadow-xl">
                </div>

                <script>
                  function openImageModal(src) {
                    document.getElementById("imageModal").classList.remove("hidden");
                    document.getElementById("imageModalContent").src = src;
                  }

                  function closeImageModal() {
                    document.getElementById("imageModal").classList.add("hidden");
                  }
                </script> -->

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
                  // buka modal
                  function openModal(id) {
                    document.getElementById(id).classList.remove("hidden");
                  }

                  // tutup modal
                  function closeModal(id) {
                    document.getElementById(id).classList.add("hidden");
                  }

                  // tutup modal kalau klik overlay (area abu-abu luar modal)
                  function overlayClose(event, id) {
                    if (event.target.id === id) {
                      closeModal(id);
                    }
                  }
                </script>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- table 2 -->
      <?php

      // Tentukan jumlah data per halaman
      $limit = 5;

      // Ambil halaman saat ini dari URL, default 1
      $pega = isset($_GET['pega']) && is_numeric($_GET['pega']) ? (int)$_GET['pega'] : 1;

      // Hitung offset
      $offset = ($pega - 1) * $limit;

      // Ambil total data untuk menghitung total halaman
      $totalStmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    WHERE t.status = 'Selesai'
");
      $totalStmt->execute();
      $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
      $totalData = $totalRow['total'];
      $totalPegas = ceil($totalData / $limit);

      // Ambil data tugas sesuai halaman
      $stmt = $conn->prepare("
    SELECT t.id_tugas, t.id_matkul, t.judul, t.deskripsi, t.deadline, 
           t.foto_opsional, t.foto_submit, t.status, t.created_at,
           mk.nama_matkul, mk.nama_pengampu,
           s.nama_semester, s.tahun_ajaran
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
    INNER JOIN semester s ON mk.id_semester = s.id_semester
    WHERE t.status = 'Selesai'
    ORDER BY t.deadline ASC
    LIMIT :limit OFFSET :offset
");
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();
      $tugas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
              <h6>Daftar Tugas - Selesai</h6>
            </div>
            <div class="flex-auto px-0 pt-0 pb-2">
              <div class="p-0 overflow-x-auto">
                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                  <thead>
                    <tr>
                      <th class="px-4 py-2 text-left">Mata Kuliah</th>
                      <th class="px-4 py-2 text-left">Judul</th>
                      <th class="px-4 py-2 text-left">Deskripsi</th>
                      <th class="px-4 py-2 text-center">Deadline</th>
                      <th class="px-4 py-2 text-center">Status</th>
                      <th class="px-4 py-2 text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($tugas)): ?>
                      <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                          Belum ada tugas selesai
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($tugas as $row): ?>
                        <tr class="border-b">
                          <td class="px-4 py-2"><?= htmlspecialchars($row['nama_matkul']) ?></td>
                          <td class="px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
                          <td class="px-4 py-2">
                            <?php
                            $raw = trim(strip_tags($row['deskripsi'] ?? ''));
                            $limit = 3; // jumlah kata maksimal yang ditampilkan

                            if ($raw === '') {
                              echo '-';
                            } else {
                              $words = preg_split('/\s+/u', $raw);
                              if (count($words) <= $limit) {
                                echo nl2br(htmlspecialchars($raw, ENT_QUOTES));
                              } else {
                                $short = implode(' ', array_slice($words, 0, $limit));
                                echo '<span title="' . htmlspecialchars($raw, ENT_QUOTES) . '">' . htmlspecialchars($short, ENT_QUOTES) . '...</span>';
                              }
                            }
                            ?>
                          </td>
                          <td class="px-4 py-2 text-center">
                            <?= $row['deadline'] ? date("d-m-Y H:i", strtotime($row['deadline'])) : '-' ?>
                          </td>
                          <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold
                              <?= $row['status'] == 'Selesai' ? 'bg-green-200 text-green-700' : ($row['status'] == 'Sedang Dikerjakan' ? 'bg-yellow-200 text-yellow-700' : 'bg-red-200 text-red-700') ?>">
                              <?= $row['status'] ?>
                            </span>
                          </td>
                          <td class="p-2 align-middle whitespace-nowrap">
                            <div class="flex gap-2">
                              <!-- Tombol Detail -->
                              <button onclick="openModal('modalDetail<?= $row['id_tugas'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-blue-600 to-cyan-400 hover:scale-105">
                                <i class="fas fa-eye"></i>
                              </button>

                              <!-- Tombol Hapus -->
                              <button onclick="openModal('modalHapus<?= $row['id_tugas'] ?>')"
                                class="px-3 py-2 font-bold text-white rounded-lg shadow-soft-md text-xs bg-gradient-to-tl from-red-600 to-pink-500 hover:scale-105">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </td>

                          <!-- simpan data modal -->
                          <?php $modalTugas[] = $row; ?>
                        </tr>
                        <!-- Modal Hapus Tugas -->
                        <div id="modalHapus<?= $row['id_tugas'] ?>"
                          class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                          role="dialog" aria-modal="true"
                          onclick="overlayClose(event, 'modalHapus<?= $row['id_tugas'] ?>')">

                          <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl relative animate-fade-in"
                            onclick="event.stopPropagation()">

                            <!-- Tombol Close -->
                            <button onclick="closeModal('modalHapus<?= $row['id_tugas'] ?>')"
                              class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                            <h2 class="text-xl font-semibold mb-6 text-gray-800">⚠️ Konfirmasi Hapus</h2>

                            <p class="mb-6 text-gray-600">
                              Apakah kamu yakin ingin menghapus tugas <b><?= htmlspecialchars($row['judul']) ?></b>?
                            </p>

                            <form method="POST" class="flex justify-end space-x-2">
                              <input type="hidden" name="reset_tugas" value="<?= $row['id_tugas'] ?>">
                              <button type="button" onclick="closeModal('modalHapus<?= $row['id_tugas'] ?>')"
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
                    <?php endif; ?>
                  </tbody>
                </table>

                <!-- Pagination Tugas Selesai -->
                <div class="mt-4 flex justify-center space-x-2">
                  <?php for ($i = 1; $i <= $totalPegas; $i++): ?>
                    <a href="?pega=<?= $i ?>&page=<?= $page ?>"
                      class="px-3 py-1 rounded border <?= $i == $pega ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' ?>">
                      <?= $i ?>
                    </a>
                  <?php endfor; ?>
                </div>

                <?php if (!empty($modalTugas)): ?>
                  <?php foreach ($modalTugas as $m): ?>
                    <!-- Modal Detail -->
                    <div id="modalDetail<?= $m['id_tugas'] ?>"
                      class="fixed inset-0 hidden bg-gray-500 bg-opacity-50 z-50 flex items-center justify-center"
                      role="dialog" aria-modal="true"
                      onclick="overlayClose(event, 'modalDetail<?= $m['id_tugas'] ?>')">

                      <div class="bg-white rounded-2xl p-8 w-full max-w-2xl shadow-2xl relative animate-fade-in
              max-h-[90vh] overflow-y-auto"
                        onclick="event.stopPropagation()">

                        <!-- Tombol Close -->
                        <button onclick="closeModal('modalDetail<?= $m['id_tugas'] ?>')"
                          class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

                        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📌 Detail Tugas</h2>

                        <form class="space-y-4">
                          <!-- Mata Kuliah -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                            <input type="text" value="<?= htmlspecialchars($m['nama_matkul']) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Judul -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Judul</label>
                            <input type="text" value="<?= htmlspecialchars($m['judul']) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Deskripsi -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea class="w-full border rounded-lg px-3 py-2 bg-gray-100" rows="4" readonly><?= htmlspecialchars($m['deskripsi']) ?></textarea>
                          </div>

                          <!-- Deadline -->
                          <div>
                            <label class="block text-sm font-medium text-gray-700">Deadline</label>
                            <input type="text" value="<?= date("d-m-Y H:i", strtotime($m['deadline'])) ?>"
                              class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                          </div>

                          <!-- Foto Opsional -->
                          <?php if (!empty($m['foto_opsional'])): ?>
                            <?php
                            $fotos = json_decode($m['foto_opsional'], true);
                            if ($fotos):
                            ?>
                              <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Tugas</label>

                                <div class="foto-scroll-container"
                                  style="width: 100%; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding-bottom: 6px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.3) transparent;">

                                  <?php foreach ($fotos as $i => $file): ?>
                                    <?php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $filePath = htmlspecialchars($file); // ✅ pakai langsung
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>

                                    <?php if ($isImage): ?>
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-block align-top mr-3"
                                        style="width: 220px; height: 140px;">
                                        <img src="<?= $filePath ?>"
                                          alt="Foto Tugas"
                                          class="w-full h-full object-cover rounded-lg border transition duration-300 group-hover:brightness-75" />
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-lg"
                                          style="background-color: rgba(0, 0, 0, 0.3);">
                                          <i class="fas fa-eye text-white text-3xl"></i>
                                        </div>
                                      </a>
                                    <?php else: ?>
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-flex flex-col items-center justify-center mr-3 bg-gray-100 border rounded-lg"
                                        style="width: 220px; height: 140px;">
                                        <?php if ($ext === 'pdf'): ?>
                                          <i class="fas fa-file-pdf text-red-500 text-6xl"></i>
                                        <?php else: ?>
                                          <i class="fas fa-file-alt text-blue-500 text-6xl"></i>
                                        <?php endif; ?>
                                        <span class="mt-2 text-sm text-gray-700 uppercase"><?= strtoupper($ext) ?> File</span>
                                      </a>
                                    <?php endif; ?>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php endif; ?>

                          <!-- Foto Submit -->
                          <?php if (!empty($m['foto_submit'])): ?>
                            <?php
                            $fotoSubmit = json_decode($m['foto_submit'], true);
                            if ($fotoSubmit):
                            ?>
                              <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Submit</label>
                                <div class="foto-scroll-container"
                                  style="width: 100%; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding-bottom: 6px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.3) transparent;">
                                  <?php foreach ($fotoSubmit as $i => $file): ?>
                                    <?php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $filePath = "uploads/jawaban/" . htmlspecialchars($file); // tanpa slash depan
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>

                                    <?php if ($isImage): ?>
                                      <!-- Jika gambar tampilkan preview -->
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-block align-top mr-3"
                                        style="width: 220px; height: 140px;">
                                        <img src="<?= $filePath ?>"
                                          alt="Foto Submit"
                                          class="w-full h-full object-cover rounded-lg border transition duration-300 group-hover:brightness-75" />
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-lg"
                                          style="background-color: rgba(0, 0, 0, 0.3);">
                                          <i class="fas fa-eye text-white text-3xl"></i>
                                        </div>
                                      </a>

                                    <?php else: ?>
                                      <!-- Jika PDF atau file lain tampilkan ikon + tipe file -->
                                      <a href="<?= $filePath ?>" target="_blank"
                                        class="relative group cursor-pointer inline-flex flex-col items-center justify-center mr-3 bg-gray-100 border rounded-lg"
                                        style="width: 220px; height: 140px;">
                                        <?php if ($ext === 'pdf'): ?>
                                          <i class="fas fa-file-pdf text-red-500 text-6xl"></i>
                                        <?php else: ?>
                                          <i class="fas fa-file-alt text-blue-500 text-6xl"></i>
                                        <?php endif; ?>
                                        <span class="mt-2 text-sm text-gray-700 uppercase"><?= strtoupper($ext) ?> File</span>
                                      </a>
                                    <?php endif; ?>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php endif; ?>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <!-- Fullscreen Image Modal -->
                <!-- <div id="imageModal" class="fixed inset-0 hidden bg-black bg-opacity-90 flex items-center justify-center" style="z-index: 99999 !important;">
                  <span class="absolute top-5 right-7 text-white text-4xl cursor-pointer" onclick="closeImageModal()">&times;</span>
                  <img id="imageModalContent"
                    class="max-w-[70vw] max-h-[70vh] w-auto h-auto object-contain rounded-lg shadow-xl mx-auto !important" />
                </div>

                <script>
                  function openImageModal(src) {
                    document.getElementById("imageModal").classList.remove("hidden");
                    document.getElementById("imageModalContent").src = src;
                  }

                  function closeImageModal() {
                    document.getElementById("imageModal").classList.add("hidden");
                  }
                </script> -->

                <script>
                  // buka modal
                  function openModal(id) {
                    document.getElementById(id).classList.remove("hidden");
                  }

                  // tutup modal
                  function closeModal(id) {
                    document.getElementById(id).classList.add("hidden");
                  }

                  // tutup modal kalau klik overlay (area abu-abu luar modal)
                  function overlayClose(event, id) {
                    if (event.target.id === id) {
                      closeModal(id);
                    }
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