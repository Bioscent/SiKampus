<?php
include '../config/db.php';

$id_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$selectedIds = isset($_GET['selected']) ? explode(',', $_GET['selected']) : [];
$selectedIds = array_map('intval', $selectedIds);

// Ambil semua mata kuliah dari semester terpilih
$stmt = $conn->prepare("
  SELECT id_matkul, nama_matkul, hari, jam_mulai, jam_selesai, ruangan
  FROM mata_kuliah
  WHERE id_semester = ?
  ORDER BY nama_matkul
");
$stmt->execute([$id_semester]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "<p class='text-gray-400 text-sm italic'>Tidak ada mata kuliah untuk semester ini.</p>";
    exit;
}

foreach ($rows as $m) {
    $id   = (int)$m['id_matkul'];
    $nama = htmlspecialchars($m['nama_matkul'], ENT_QUOTES);
    $hari = htmlspecialchars($m['hari'] ?? '', ENT_QUOTES);
    $jm   = htmlspecialchars($m['jam_mulai'] ?? '', ENT_QUOTES);
    $js   = htmlspecialchars($m['jam_selesai'] ?? '', ENT_QUOTES);
    $rg   = htmlspecialchars($m['ruangan'] ?? '', ENT_QUOTES);

    $checked = in_array($id, $selectedIds) ? 'checked' : '';

    echo "
    <label class='flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded border'>
      <input type='checkbox' name='id_matkul[]' value='$id' $checked
             data-id='$id'
             data-nama='$nama'
             data-hari='$hari'
             data-jm='$jm'
             data-js='$js'
             data-ruang='$rg'>
      <div>
        <p class='font-medium text-gray-800'>$nama</p>
        <p class='text-xs text-gray-500'>$hari " . ($jm ? "$jm - $js" : '') . " " . ($rg ? "($rg)" : '') . "</p>
      </div>
    </label>
  ";
}
