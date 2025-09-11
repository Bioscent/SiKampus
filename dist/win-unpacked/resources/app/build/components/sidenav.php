<?php
function navItem($id, $label, $url, $activePage, $svg)
{
    $isActive = ($activePage == $id);

    $linkClass = $isActive
        ? "bg-white font-semibold text-slate-700 shadow-soft-xl"
        : "text-slate-400 hover:bg-gray-100 hover:text-slate-700";

    $iconClass = $isActive
        ? "bg-gradient-to-tl from-purple-700 to-pink-500 shadow-soft-2xl text-white"
        : "bg-white shadow-soft-xl text-black hover:bg-slate-200 hover:text-slate-700";

    echo '
    <li class="mt-0.5 w-full">
        <a href="' . $url . '" 
           class="py-2.7 text-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap rounded-lg px-4 transition-colors duration-200 ' . $linkClass . '">
            
            <div class="' . $iconClass . ' mr-2 flex h-8 w-8 items-center justify-center rounded-lg xl:p-2.5 transition-colors duration-200">
                ' . $svg . '
            </div>

            <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">' . $label . '</span>
        </a>
    </li>';
}
?>

<!-- tombol hamburger -->
<button id="open-sidenav" class="fixed top-4 left-4 z-50 p-2 bg-white rounded-lg shadow-md xl:hidden">
    <i class="fas fa-bars text-black text-xl"></i>
</button>

<!-- sidenav  -->
<aside id="sidenav"
    class="max-w-62.5 ease-nav-brand z-40 fixed inset-y-0 my-4 ml-4 -translate-x-full flex-wrap items-center justify-between overflow-y-auto rounded-2xl border-0 bg-white p-0 antialiased shadow-none transition-transform duration-200 xl:left-0 xl:translate-x-0 xl:bg-transparent">
    <div class="h-auto relative">
        <!-- Logo dan Judul -->
        <a class="flex flex-col items-center px-8 py-6 text-slate-700 transition-all duration-200" href="">
            <img src="../assets/img/logo-alt-bgremoved.png" class="h-20 w-auto" alt="logo_sikampus" />
            <span class="mt-2 text-xl font-bold tracking-wide">SiKampus</span>
        </a>

        <!-- Tombol close -->
        <button id="close-sidenav"
            class="absolute top-4 right-4 p-2 xl:hidden">
            <i class="fas fa-times text-slate-400 text-lg"></i>
        </button>

        <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />
    </div>

    <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
        <ul class="flex flex-col pl-0 mb-0">
            <?php
            navItem(
                "dashboard",
                "Dashboard",
                "/pages/dashboard.php",
                $activePage,
                '<svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h10v10H3V3zm12 0h6v6h-6V3zM15 11h6v10h-6V11zM3 15h10v6H3v-6z"/></svg>'
            );

            navItem(
                "tables",
                "Jadwal",
                "/pages/tables.php",
                $activePage,
                '<svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 6v12H5V8h14zM7 10h5v5H7v-5z"/></svg>'
            );

            navItem(
                "tugas",
                "Tugas",
                "/pages/tugas.php",
                $activePage,
                '<svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0 4h5v2H8v-2z"/></svg>'
            );

            navItem(
                "matkul",
                "Matkul",
                "/pages/matkul.php",
                $activePage,
                '<svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm0 18H6V4h12v16zM8 6h8v2H8V6z"/></svg>'
            );
            ?>
        </ul>
    </div>
</aside>

<!-- Script toggle sidenav -->
<script>
    const sidenav = document.getElementById('sidenav');
    const openBtn = document.getElementById('open-sidenav');
    const closeBtn = document.getElementById('close-sidenav');

    openBtn.addEventListener('click', () => {
        sidenav.classList.remove('-translate-x-full');
    });

    closeBtn.addEventListener('click', () => {
        sidenav.classList.add('-translate-x-full');
    });
</script>