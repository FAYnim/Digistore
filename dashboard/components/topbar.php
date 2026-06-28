<header class="sticky top-0 z-20 border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
  <div class="flex h-14 items-center gap-3 px-4 sm:px-5 lg:px-6">
    <button id="openSidebar" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 lg:hidden" type="button"><i class="fa-solid fa-bars"></i></button>
    <div class="min-w-0 flex-1">
      <h1 class="truncate text-lg font-black tracking-tight text-slate-950 dark:text-white"><?= $pageTitle ?></h1>
    </div>
    <button id="themeToggle" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200" type="button"><i class="fa-solid fa-circle-half-stroke"></i></button>
    <div class="hidden items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900 sm:flex">
      <div class="text-right leading-tight">
        <div class="text-sm font-black text-slate-800 dark:text-slate-100"><?= htmlspecialchars($_SESSION['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <a class="text-sm font-black text-red-600 hover:text-red-700 dark:text-red-400" href="logout.php">Logout</a>
    </div>
  </div>
</header>
