<?php require base_path("views/partials/head.php") ?>
<?php require base_path("views/partials/nav.php") ?>
<?php require base_path("views/partials/banner.php") ?>

    <main>

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
         
        <form method="POST" action="/note">

        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= $note['id'] ?>">

        <div class="border-b border-gray-900/10 pb-12">

            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="col-span-full mt-2">


                    <label for="username" class="block text-base/6 font-medium text-gray-900">Username</label>
                    <input id="username" type="text" name="username" value="<?= htmlspecialchars($note['username']) ?>" class="block w-80 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6 mb-4" />

                    <label for="first-name" class="block text-base/6 font-medium text-gray-900">First Name</label>
                    <input id="first-name" type="text" name="first-name" value="<?= htmlspecialchars($note['first_name']) ?>" class="block w-80 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6 mb-4" />

                    <label for="last-name" class="block text-base/6 font-medium text-gray-900">Last Name</label>
                    <input id="last-name" type="text" name="last-name" value="<?= htmlspecialchars($note['last_name']) ?>" class="block w-80 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6 mb-4" />

                    <label for="about" class="block text-base/6 font-medium text-gray-900">About</label>
                    <textarea name="about" id="about" rows="2" class="block w-8/12 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6 mb-4" placeholder="About..."><?= htmlspecialchars($note['about']) ?></textarea>

                    <label for="body" class="block text-base/6 mb-2 font-medium text-gray-900">Body</label>
                    <textarea name="body" id="body" rows="3" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="Here's an idea for a note..."><?= htmlspecialchars($note['body']) ?></textarea>

                    <?php if (isset($errors['body'])) : ?>
                        <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['body'] ?> </p>
                    <?php endif ?>

                    <div class="pt-2 flex items-center justify-end gap-x-2">
                        <button type="submit" class="rounded-md bg-slate-500 my-2 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <a href="/notes"> Go Back</a>
                        </button>
                        <button type="submit" class="rounded-md bg-indigo-600 mx-3 my-2 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Update
                        </button>
                    </div>

                </div>
                
            </div>
        
        </div>

        </form>

        </div>
    </main>

<?php require base_path("views/partials/footer.php") ?>