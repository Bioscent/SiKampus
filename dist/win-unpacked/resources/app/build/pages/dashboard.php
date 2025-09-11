<?php
include '../config/db.php';

// Hitung tugas selesai
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tugas WHERE status = 'Selesai'");
$stmt->execute();
$selesai = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Hitung tugas belum selesai (Belum Dikerjakan + Sedang Dikerjakan)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tugas WHERE status != 'Selesai'");
$stmt->execute();
$belum = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Ambil data presensi
$stmt = $conn->prepare("
  SELECT p.id_presensi, p.status, p.keterangan, 
         DATE(p.created_at) as tanggal,
         m.nama_matkul, m.nama_pengampu, m.jam_mulai, m.jam_selesai, m.ruangan
  FROM presensi p
  JOIN jadwal j ON p.id_jadwal = j.id_jadwal
  JOIN mata_kuliah m ON j.id_matkul = m.id_matkul
  ORDER BY p.created_at ASC
");
$stmt->execute();
$presensi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($presensi as $row) {
  $color = match ($row['status']) {
    'Hadir' => '#22c55e',        // hijau
    'Tidak Hadir' => '#ef4444',  // merah
    'Izin' => '#eab308',         // kuning
    'Diganti' => '#3b82f6',      // biru
    default => '#6b7280',
  };

  $events[] = [
    'title' => $row['nama_matkul'] . " (" . $row['status'] . ")",
    'start' => $row['tanggal'],
    'allDay' => true,
    'extendedProps' => [
      'matkul' => $row['nama_matkul'],
      'dosen' => $row['nama_pengampu'],
      'jam'   => $row['jam_mulai'] . " - " . $row['jam_selesai'],
      'ruangan' => $row['ruangan'],
      'status' => $row['status'],
      'keterangan' => $row['keterangan']
    ],
    'color' => $color
  ];
}

// JURNAL
$limit = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ambil total data
$totalStmt = $conn->query("
    SELECT COUNT(*) 
    FROM jurnal j
    JOIN jadwal jd ON j.id_jadwal = jd.id_jadwal
    JOIN mata_kuliah mk ON jd.id_matkul = mk.id_matkul
");
$totalJurnal = $totalStmt->fetchColumn();
$totalPages = ceil($totalJurnal / $limit);

// ambil data jurnal sesuai halaman
$stmt = $conn->prepare("
    SELECT j.id_jurnal, j.catatan, j.created_at, mk.nama_matkul
    FROM jurnal j
    JOIN jadwal jd ON j.id_jadwal = jd.id_jadwal
    JOIN mata_kuliah mk ON jd.id_matkul = mk.id_matkul
    ORDER BY j.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jurnals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<script>
  const presensiEvents = <?= json_encode($events) ?>;
</script>

<!DOCTYPE html>
<html>

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

<body class="m-0 font-sans antialiased font-normal text-base leading-default bg-gray-50 text-slate-500">

  <!-- sidenav  -->
  <?php
  $activePage = "dashboard";
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
            <li class="text-sm pl-2 capitalize leading-normal text-slate-700 before:float-left before:pr-2 before:text-gray-600 before:content-['/']" aria-current="page">Dashboard</li>
          </ol>
          <h6 class="mb-0 font-bold capitalize">Dashboard</h6>
        </nav>
      </div>
    </nav>

    <!-- end Navbar -->

    <!-- cards -->
    <div class="w-full px-6 py-6 mx-auto">
      <!-- row 1 -->
      <div class="flex flex-wrap -mx-3">

        <!-- Card Tugas Selesai -->
        <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
          <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="flex-auto p-4">
              <div class="flex flex-row -mx-3">
                <div class="flex-none w-2/3 max-w-full px-3">
                  <div>
                    <p class="mb-0 font-sans font-semibold leading-normal text-sm">Tugas Selesai</p>
                    <h5 class="mb-0 font-bold">
                      <?= $selesai ?>
                    </h5>
                  </div>
                </div>
                <div class="px-3 text-right basis-1/3">
                  <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-tl from-green-500 to-lime-400">
                    <i class="fa-solid fa-circle-check text-lg text-white"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Tugas Belum Selesai -->
        <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
          <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="flex-auto p-4">
              <div class="flex flex-row -mx-3">
                <div class="flex-none w-2/3 max-w-full px-3">
                  <div>
                    <p class="mb-0 font-sans font-semibold leading-normal text-sm">Tugas Belum Selesai</p>
                    <h5 class="mb-0 font-bold">
                      <?= $belum ?>
                    </h5>
                  </div>
                </div>
                <div class="px-3 text-right basis-1/3">
                  <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-tl from-red-500 to-pink-500">
                    <i class="fa-solid fa-circle-xmark text-lg text-white"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- cards row 3 -->

      <div class="flex flex-wrap mt-6 -mx-3">

        <!-- Kolom Kalender -->
        <div class="w-full lg:w-1/2 px-3">
          <div class="bg-white shadow-soft-xl rounded-2xl p-6">
            <h6 class="font-bold mb-4 text-center">📅 Kalender Presensi</h6>
            <div id="calendar" class="text-xs"></div>
          </div>
        </div>

        <!-- Kolom Jurnal -->
        <div class="w-full lg:w-1/2 px-3 mt-6 lg:mt-0">
          <div class="bg-white shadow-soft-xl rounded-2xl p-6 h-full">
            <h6 class="font-bold mb-4 text-center">📖 Jurnal Perkuliahan</h6>

            <div class="space-y-4">
              <?php if (!empty($jurnals)): ?>
                <?php foreach ($jurnals as $j): ?>
                  <div class="border rounded-xl p-4 bg-gray-50 shadow-sm">
                    <p class="font-semibold text-gray-800">
                      <?= htmlspecialchars($j['nama_matkul']) ?>
                    </p>
                    <p class="text-sm text-gray-600">
                      Belajar tentang: <?= !empty($j['catatan']) ? nl2br(htmlspecialchars($j['catatan'])) : '-' ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      <?= date("d F Y H:i", strtotime($j['created_at'])) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-gray-600 text-center">Belum ada jurnal perkuliahan.</p>
              <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
              <div class="flex justify-center items-center space-x-2 mt-4">
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <a href="?page=<?= $i ?>"
                    class="px-3 py-1 rounded-lg <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                    <?= $i ?>
                  </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300">Next »</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- FullCalendar -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

        <style>
          #calendar {
            font-size: 12px;
          }

          .fc .fc-daygrid-day-number {
            font-size: 11px;
            padding: 2px;
          }

          .fc .fc-event {
            font-size: 10px;
            padding: 1px 2px;
          }
        </style>
        <style>
          /* Pastikan overlay modal muncul */
          #modalPresensi {
            background-color: rgba(0, 0, 0, 0.5) !important;
            /* abu-abu transparan */
            backdrop-filter: blur(2px) !important;
            /* blur lembut */
          }
        </style>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
              initialView: 'dayGridMonth',
              locale: 'id',
              events: presensiEvents,
              eventClick: function(info) {
                const clickedDate = info.event.startStr.split("T")[0];
                const eventsToday = presensiEvents.filter(ev => ev.start === clickedDate);

                let detailHTML = '';
                if (eventsToday.length > 0) {
                  eventsToday.forEach(ev => {
                    detailHTML += `
        <div class="border rounded-xl p-4 bg-gray-50 shadow-sm">
          <div class="space-y-2">
            <div>
              <label class="block text-xs font-medium text-gray-500">Mata Kuliah</label>
              <p class="font-semibold text-gray-800">${ev.extendedProps.matkul}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500">Dosen</label>
              <p class="text-gray-700">${ev.extendedProps.dosen}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500">Jam</label>
              <p class="text-gray-700">${ev.extendedProps.jam}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500">Ruangan</label>
              <p class="text-gray-700">${ev.extendedProps.ruangan}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500">Status</label>
              <p class="font-bold" style="color:${ev.color}">${ev.extendedProps.status}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500">Keterangan</label>
              <p class="text-gray-700">${ev.extendedProps.keterangan ?? '-'}</p>
            </div>
          </div>
        </div>
      `;
                  });
                } else {
                  detailHTML = `<p class="text-gray-600">Tidak ada presensi di tanggal ini.</p>`;
                }

                document.getElementById('presensiDetail').innerHTML = detailHTML;
                openModal('modalPresensi');
              }
            });
            calendar.render();
          });

          function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
          }

          function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
          }

          function overlayClose(event, id) {
            if (event.target.id === id) {
              closeModal(id);
            }
          }
        </script>

        <!-- Modal Presensi -->
        <div id="modalPresensi"
          class="hidden fixed inset-0 z-50 flex items-center justify-center"
          role="dialog" aria-modal="true"
          onclick="overlayClose(event, 'modalPresensi')">

          <!-- Konten Modal -->
          <div class="bg-white rounded-2xl p-8 w-full max-w-4xl shadow-2xl relative animate-fade-in
              max-h-[90vh] overflow-y-auto"
            onclick="event.stopPropagation()">

            <!-- Tombol Close -->
            <button type="button" onclick="closeModal('modalPresensi')"
              class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>

            <h2 class="text-2xl font-semibold mb-6 text-gray-800">📅 Detail Presensi</h2>

            <!-- Kontainer Presensi -->
            <div id="presensiDetail" class="grid gap-4 sm:grid-cols-2"></div>
          </div>
        </div>
      </div>

      <!-- footer  -->
      <?php include '../components/footer.php'; ?>

    </div>
    <!-- end cards -->
  </main>
</body>
<!-- plugin for charts  -->
<script src="../assets/js/plugins/chartjs.min.js" async></script>
<!-- plugin for scrollbar  -->
<script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
<!-- github button -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- main script file  -->
<script src="../assets/js/soft-ui-dashboard-tailwind.js?v=1.0.5" async></script>

</html>