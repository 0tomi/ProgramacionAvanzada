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

                    <label for="body" class="block text-base/6 mb-2 font-medium text-gray-900">Body</label>
                
                    <textarea 
                    name="body" 
                    id="body" 
                    rows="3" 
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                    placeholder="Here's an idea for a note..."><?= $note['body'] ?></textarea>

                    <?php if (isset($errors['body'])) : ?>
                        <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['body'] ?> </p>
                    <?php endif ?>

                    <div class="pt-2 flex items-center justify-end gap-x-2">

                        <button 
                        type="submit" 

                        class="rounded-md bg-slate-500 my-2 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            
                            <a href="/notes"> Go Back</a>
                        </button>

                        <button 
                        type="submit" 

                        class="rounded-md bg-indigo-600 mx-3 my-2 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            
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