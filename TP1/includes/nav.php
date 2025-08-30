<nav class="bg-gray-800 drop-shadow-xl">

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

    <div class="flex h-16 items-center justify-between">

        <div class="flex items-center">

            <div class="shrink-0">
                <img class="size-8" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">
            </div>

            <!-- Solo logo, sin opciones de navegación -->

        </div>

        <div class="hidden md:block">

            <div class="ml-4 flex items-center md:ml-6">

                <button type="button" class="relative rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800 focus:outline-hidden">

                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">View notifications</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    
                </button>

                
            

                <!-- Profile dropdown -->
                <div class="relative ml-3">
                    
                    <button type="button" class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm  focus:outline-hidden" id="user-menu-button">

                        <?php if($_SESSION["username"] ?? false): ?>
                        <div class="mr-2 bg-slate-900 rounded-full size-10 flex items-center justify-center">
                            <img class="size-8 rounded-full" src="imagenes/profile.png" alt="Profile">
                        </div>
                        <?php else: ?>
                        <div class="bg-white rounded-full size-9 flex items-center justify-center mr-6">
                            <img class="size-8 rounded-full" src="imagenes/user.png" alt="Guest">
                        </div> 
                        <a href="/register.php" class="text-gray-700 bg-slate-300 rounded-xl shadow-lg mr-5 items-center justify-center duration-5 w-24 h-10 flex hover:scale-105 transition-transform">Register</a>
                        <a href="/login.php" class="text-gray-700 bg-slate-300 rounded-xl shadow-lg  items-center justify-center duration-5 w-24 h-10 flex hover:scale-105 transition-transform">Log In</a>
                        <?php endif; ?>
                    </button>

                    
                </div>

                <?php if($_SESSION['username'] ?? false) : ?>
                <div class="ml-3">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="_method" value="DELETE">

                            <button class="px-4 py-1 hover:scale-105 transition-transform bg-slate-300 shadow-md rounded-xl ">Log Out</button>
                        </form>
                </div>
                <?php endif; ?>

                <!--
                Dropdown menu, show/hide based on menu state.

                Entering: "transition ease-out duration-100"
                    From: "transform opacity-0 scale-95"
                    To: "transform opacity-100 scale-100"
                Leaving: "transition ease-in duration-75"
                    From: "transform opacity-100 scale-100"
                    To: "transform opacity-0 scale-95"
                -->
                
                <!--
                <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                
                <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</a>
                </div>
                -->
            </div>

        </div>

    </div>
    
</div>


</nav>