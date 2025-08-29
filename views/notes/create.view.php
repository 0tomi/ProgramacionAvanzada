<?php require base_path("views/partials/head.php") ?>
<?php require base_path("views/partials/nav.php") ?>
<?php require base_path("views/partials/banner.php") ?>
<?php require base_path("views/partials/dashboard.php") ?>

<main style="margin-left:220px;">

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <form method="post" id="noteForm" onsubmit="return validateNoteForm()">

        <div class="border-b border-gray-900/10 pb-12">

            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="col-span-full mt-2">

                <h2 class="text-base/7 font-semibold text-gray-300">Profile</h2>
                
                <p class="mt-1 text-sm/6 text-gray-400">This information will be displayed publicly so be careful what you share.</p>

                    <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-4">

                        <label for="username" class="block text-base/6 font-medium text-gray-300">Username</label>

                        <div class="mt-2">
                            
                            <div class="w-80 items-center rounded-md bg-white pl-3">

                            <input id="username" type="text" name="username" placeholder="pablito clavito" class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" required minlength="3" />

                            </div>
                        </div>

                        </div>
                    </div>

                <h2 class="mt-6 text-base/7 font-semibold text-gray-300">Personal Information</h2>

                <div class="mt-4 grid grid-cols-1 gap-x-18 gap-y-8 sm:grid-cols-2">

                    <div class="sm:col-span-1">

                    <label for="first-name" class="block text-sm/6 font-medium text-gray-300">First name</label>

                        <div class="mt-2">
                            <input id="first-name" type="text" name="first-name" autocomplete="given-name" class="block w-80 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required minlength="2" />
                        </div>

                    </div>

                    <div class="sm:col-span-2">

                    <label for="last-name" class="block text-sm/6 font-medium text-gray-300">Last name</label>

                        <div class="mt-2">
                            <input id="last-name" type="text" name="last-name" autocomplete="family-name" class="block w-80 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required minlength="2" />
                        </div>

                    </div>

                </div>


                


                <label for="body" class="mt-6 block text-base/6 mb-2 font-medium text-gray-300">About</label>
            
                <textarea 
                name="body" 
                id="body" 
                rows="3" 
                class="block w-8/12 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                placeholder="Here's an idea for a note..." required minlength="10"><?= $_POST['body'] ?? '' ?></textarea>

                <?php if (isset($errors['body'])) : ?>
                    <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['body'] ?> </p>
                <?php endif ?>

                    <div class=" pt-2 mt-4 flex items-center justify-start gap-x-4">

                        <button 
                        type="submit" 

                        class="rounded-md bg-indigo-600 my-2 px-5 py-2 text-base font-semibold text-white shadow-xs hover:bg-indigo-500 hover:scale-105 transition-transform focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            
                            <a href="/notes"> Go Back</a>
                        </button>

                        <button 
                        type="submit" 

                        class="rounded-md bg-indigo-600 mx-3 my-2 px-5 py-2 text-base font-semibold text-white shadow-xs hover:bg-indigo-500 hover:scale-105 transition-transform focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"> 
                            Create
                        </button>
                    </div>

                </div>
                
            </div>
        
        </div>

        </form>

        </div>
    </main>

<script>
function validateNoteForm() {
    let username = document.getElementById('username').value.trim();
    let firstName = document.getElementById('first-name').value.trim();
    let lastName = document.getElementById('last-name').value.trim();
    let body = document.getElementById('body').value.trim();
    let errors = [];

    if (username.length < 3) {
        errors.push('El nombre de usuario debe tener al menos 3 caracteres.');
    }
    if (firstName.length < 2) {
        errors.push('El nombre debe tener al menos 2 caracteres.');
    }
    if (lastName.length < 2) {
        errors.push('El apellido debe tener al menos 2 caracteres.');
    }
    if (body.length < 10) {
        errors.push('La nota debe tener al menos 10 caracteres.');
    }

    if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
    }
    return true;
}
</script>
<?php require base_path("views/partials/footer.php") ?>