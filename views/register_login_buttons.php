<?php
// Este archivo muestra el botón para ir a Login en Register y viceversa
if ($_SERVER['REQUEST_URI'] === '/register') {
    echo '<div class="mt-8 flex justify-center"><a href="/login" class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow hover:bg-indigo-500 transition">Ir a Login</a></div>';
} elseif ($_SERVER['REQUEST_URI'] === '/login') {
    echo '<div class="mt-8 flex justify-center"><a href="/register" class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow hover:bg-indigo-500 transition">Ir a Registrarse</a></div>';
}
