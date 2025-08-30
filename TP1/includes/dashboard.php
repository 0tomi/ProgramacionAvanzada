<button id="dashboard-toggle" class="fixed top-20 ml-6 mt-1 left-2 z-50 bg-indigo-600 text-white px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 border-none cursor-pointer hover:scale-105 transition-transform">
    <span id="dashboard-toggle-icon">☰</span> <span class="text-base">Menú</span>
</button>

<aside id="dashboard-sidebar" class="w-56 bg-gradient-to-b from-slate-300 via-slate-400 to-slate-500 fixed top-0 left-0 hidden flex-col items-start p-8 shadow-lg z-40 h-full">

    <h2 class="text-xl font-bold mb-8">Dashboard</h2>

    <nav class="w-full gap-y-2 mt-14">

        <ul class="list-none p-0 m-0 w-full">
            <li>
                <img class="size-24 ml-8 mb-4 rounded-full" src="imagenes/profile.png" alt="Profile">
            </li>
            
            <li class="mb-3"><a href="inicio.php" class="no-underline text-gray-800 text-lg flex items-center gap-2 hover:text-indigo-600 hover:scale-105 transition-transform">🏠 <span>Home</span></a></li>

            <li class="mb-3"><a href="about.php" class="no-underline text-gray-800 text-lg flex items-center gap-2 hover:text-indigo-600 hover:scale-105 transition-transform">ℹ️ <span>About</span></a></li>

            <li class="mb-3"><a href="POSTS/index.php" class="no-underline text-gray-800 text-lg flex items-center gap-2 hover:text-indigo-600 hover:scale-105 transition-transform">ℹ️ <span>Posts</span></a></li>

        </ul>

    </nav>

</aside>

<script>

    const dashboardToggle = document.getElementById('dashboard-toggle');

    const dashboardSidebar = document.getElementById('dashboard-sidebar');

    const dashboardToggleIcon = document.getElementById('dashboard-toggle-icon');

    let dashboardOpen = false;

    dashboardToggle.addEventListener('click', function() {
        
        dashboardOpen = !dashboardOpen;
        
        dashboardSidebar.style.display = dashboardOpen ? 'flex' : 'none';
        
        dashboardToggleIcon.textContent = dashboardOpen ? '✖' : '☰';
    });

</script>