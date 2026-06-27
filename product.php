<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Detail produk digital DigiStore.">
  <title>Detail Produk — DigiStore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark" || (!savedTheme && matchMedia("(prefers-color-scheme: dark)").matches)) document.documentElement.classList.add("dark");
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[var(--bg)] text-[var(--text)] antialiased">
  <header class="sticky top-0 z-50 px-4 py-4 lg:px-8">
    <nav class="mx-auto flex max-w-7xl items-center gap-4" aria-label="Navigasi produk">
      <a href="index.php#produk" class="brand-pill">
        <span class="brand-mark"><i class="fa-solid fa-cubes-stacked"></i></span>
        <span>DigiStore</span>
      </a>
      <div class="nav-shell hidden md:flex">
        <a class="nav-link" href="index.php#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link" href="order-status.php"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
        <button id="menuToggle" class="icon-btn md:hidden" type="button" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
    <div id="mobileMenu" class="mobile-menu hidden md:hidden">
      <div class="grid gap-2 text-sm font-semibold">
        <a class="nav-link" href="index.php#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link" href="order-status.php"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-2xl px-4 py-8 lg:px-8">
    <div id="productDetail">
      <div class="text-center py-12 text-[var(--muted)]">Memuat produk...</div>
    </div>
  </main>

  <footer class="border-t border-[var(--border)] px-4 py-8 lg:px-8">
    <p class="mx-auto max-w-2xl text-sm text-[var(--muted)]">© 2026 DigiStore. All rights reserved.</p>
  </footer>

  <script src="assets/js/api.js"></script>
  <script>
    function escapeText(str) {
      const d = document.createElement('div');
      d.textContent = str;
      return d.innerHTML;
    }

    function formatPrice(n) {
      return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    async function loadProduct() {
      const params = new URLSearchParams(window.location.search);
      const slug = params.get('slug');
      const el = document.getElementById('productDetail');

      if (!slug) {
        el.innerHTML = '<div class="text-center py-12"><p class="text-red-500">Produk tidak ditemukan.</p><a href="index.php" class="text-blue-500 underline mt-3 inline-block">Kembali ke beranda</a></div>';
        return;
      }

      try {
        const res = await apiGet('/products.php?slug=' + encodeURIComponent(slug));
        if (!res.success || !res.data) {
          el.innerHTML = '<div class="text-center py-12"><p class="text-red-500">Produk tidak ditemukan.</p><a href="index.php" class="text-blue-500 underline mt-3 inline-block">Kembali ke beranda</a></div>';
          return;
        }

        const p = res.data;
        const hasDiscount = p.original_price && p.original_price > p.price;

        el.innerHTML = `
          <div class="overflow-hidden rounded-2xl bg-[var(--card)] border border-[var(--border)]">
            ${p.image_url
              ? '<img src="' + escapeText(p.image_url) + '" alt="' + escapeText(p.name) + '" class="w-full h-64 object-cover">'
              : '<div class="flex h-64 items-center justify-center bg-[var(--bg)] text-[var(--muted)]"><i class="fa-regular fa-image text-3xl"></i></div>'
            }

            <div class="space-y-4 p-6">
              <div>
                <h1 class="font-display text-2xl font-extrabold">${escapeText(p.name)}</h1>
              </div>

              <div class="flex items-center gap-3 text-sm text-[var(--muted)]">
                ${p.rating > 0 ? '<span>★ ' + p.rating + '</span>' : ''}
                ${p.sold_count > 0 ? '<span>' + p.sold_count + ' terjual</span>' : ''}
              </div>

              <div class="flex items-center gap-3">
                <span class="text-2xl font-bold">${formatPrice(p.price)}</span>
                ${hasDiscount ? '<span class="text-lg text-[var(--muted)] line-through">' + formatPrice(p.original_price) + '</span>' : ''}
              </div>

              ${p.description ? '<div class="leading-relaxed whitespace-pre-line text-[var(--muted)]">' + escapeText(p.description) + '</div>' : ''}

              <div class="text-sm text-[var(--muted)]">
                Stok: <span class="font-semibold ${p.stock > 0 ? 'text-green-500' : 'text-red-500'}">${p.stock > 0 ? p.stock : 'Habis'}</span>
              </div>

              <div class="pt-2">
                ${p.stock > 0
                  ? '<a href="checkout.php?product=' + encodeURIComponent(p.slug) + '" class="primary-btn inline-flex w-full text-center">Beli Sekarang</a>'
                  : '<button disabled class="primary-btn inline-flex w-full text-center opacity-50 cursor-not-allowed">Stok Habis</button>'
                }
              </div>

              <a href="index.php" class="block text-center text-sm text-[var(--muted)] hover:underline">← Kembali ke katalog</a>
            </div>
          </div>
        `;

        document.title = escapeText(p.name) + ' — DigiStore';
      } catch (err) {
        el.innerHTML = '<div class="text-center py-12"><p class="text-red-500">Gagal memuat produk.</p><a href="index.php" class="text-blue-500 underline mt-3 inline-block">Kembali ke beranda</a></div>';
      }
    }

    document.getElementById('themeToggle')?.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });

    document.getElementById('menuToggle')?.addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.toggle('hidden');
    });

    loadProduct();
  </script>
</body>
</html>
