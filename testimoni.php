<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Testimoni pelanggan DigiStore.">
  <title>Testimoni — DigiStore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark" || (!savedTheme && matchMedia("(prefers-color-scheme: dark)").matches)) document.documentElement.classList.add("dark");
  </script>
  <link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
  <link rel="apple-touch-icon" href="assets/favicon/apple-touch-icon.png">
  <link rel="manifest" href="assets/favicon/site.webmanifest">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[var(--bg)] text-[var(--text)] antialiased">
  <header class="sticky top-0 z-50 px-4 py-4 lg:px-8">
    <nav class="mx-auto flex max-w-7xl items-center gap-4" aria-label="Navigasi testimoni">
      <a href="index" class="brand-pill">
        <span data-store-name>DigiStore</span>
      </a>
      <div class="nav-shell hidden md:flex">
        <a class="nav-link" href="index#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link active" href="testimoni"><i class="fa-regular fa-comments"></i><span>Testimoni</span></a>
        <a class="nav-link" href="order-status"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
        <button id="menuToggle" class="icon-btn md:hidden" type="button" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
    <div id="mobileMenu" class="mobile-menu hidden md:hidden">
      <div class="grid gap-2 text-sm font-semibold">
        <a class="nav-link" href="index#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link active" href="testimoni"><i class="fa-regular fa-comments"></i><span>Testimoni</span></a>
        <a class="nav-link" href="order-status"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
    </div>
  </header>

  <main>
    <div class="testimoni-hero">
      <h1>Apa Kata Pembeli?</h1>
      <p>Testimoni asli dari pelanggan yang sudah menggunakan produk DigiStore.</p>
    </div>
    <section class="section" style="padding-top:1rem">
      <div id="testimonialGrid" class="grid gap-5 lg:grid-cols-4"></div>
      <div id="testimonialLoader" class="mt-8 text-center" style="display:none">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-[var(--border)] border-t-[var(--accent)]" role="status"><span class="sr-only">Memuat...</span></div>
      </div>
      <div id="testimonialLoadMore" class="mt-8 text-center">
        <button id="loadMoreBtn" class="secondary-btn" type="button">Muat Lebih Banyak</button>
      </div>
      <div id="testimonialEmpty" class="empty-state hidden"><h3>Tidak ada lagi testimoni.</h3></div>
    </section>
  </main>

  <footer class="border-t border-[var(--border)] px-4 py-8 lg:px-8">
    <p class="mx-auto max-w-7xl text-sm text-[var(--muted)]">© 2026 <span data-store-name>DigiStore</span>. All rights reserved.</p>
  </footer>

  <script src="assets/js/api.js"></script>
  <script>
    const PAGE_LIMIT = 12;
    let offset = 0;
    let loading = false;
    let hasMore = true;

    function escapeText(str) {
      const d = document.createElement('div');
      d.textContent = str;
      return d.innerHTML;
    }

    function getInitials(name) {
      return (name || '?').split(/\s+/).map(w => w[0]).join('').toUpperCase().slice(0, 2) || '?';
    }

    function avatarColor(name) {
      const colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#06b6d4','#f97316'];
      let hash = 0;
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    }

    function openLightbox(src) {
      const exists = document.querySelector('.lightbox-overlay');
      if (exists) exists.remove();
      const overlay = document.createElement('div');
      overlay.className = 'lightbox-overlay';
      overlay.innerHTML = '<button class="lightbox-close" type="button" aria-label="Tutup">&times;</button><img src="' + escapeText(src) + '" alt="Testimoni" onclick="event.stopPropagation()">';
      overlay.addEventListener('click', () => overlay.remove());
      overlay.querySelector('.lightbox-close').addEventListener('click', (e) => { e.stopPropagation(); overlay.remove(); });
      document.body.appendChild(overlay);
    }

    function renderTestimonialCard(item) {
      if (item.image_path) {
        return '<div class="testimonial-image-card" onclick="openLightbox(\'' + escapeText(item.image_path) + '\')"><img src="' + escapeText(item.image_path) + '" alt="Testimoni ' + escapeText(item.name) + '" loading="lazy"><div class="testimonial-overlay"><div class="testimonial-name">' + escapeText(item.name) + '</div><div class="testimonial-role">' + escapeText(item.role || 'Pelanggan') + '</div></div></div>';
      }
      const stars = '<span class="testimonial-rating">' + '\u2605'.repeat(Math.min(5, Math.max(0, Number(item.rating || 0)))) + '</span>';
      return '<article class="testimonial-card"><div class="testimonial-header"><div class="testimonial-avatar" style="background:' + avatarColor(item.name) + '">' + getInitials(item.name) + '</div><div><div class="testimonial-name">' + escapeText(item.name) + '</div><div class="testimonial-role">' + escapeText(item.role || 'Pelanggan') + '</div></div>' + stars + '</div><p class="testimonial-message">"' + escapeText(item.message) + '"</p></article>';
    }

    async function loadTestimonials(reset = false) {
      if (loading) return;
      if (reset) { offset = 0; hasMore = true; $('#testimonialGrid').innerHTML = ''; }

      if (!hasMore) return;

      loading = true;
      $('#testimonialLoader').style.display = 'block';
      $('#testimonialLoadMore').style.display = 'none';

      try {
        const res = await apiGet('/testimonials-all?offset=' + offset + '&limit=' + PAGE_LIMIT);
        if (!res.success || !res.data) throw new Error(res.message);

        const items = res.data.data || [];
        hasMore = res.data.has_more || false;
        offset += items.length;

        $('#testimonialGrid').innerHTML += items.map(renderTestimonialCard).join('');
        $('#testimonialLoader').style.display = 'none';

        if (hasMore) {
          $('#testimonialLoadMore').style.display = 'block';
          $('#testimonialEmpty').classList.add('hidden');
        } else {
          $('#testimonialLoadMore').style.display = 'none';
          if (offset === 0) $('#testimonialEmpty').classList.remove('hidden');
        }
      } catch (err) {
        $('#testimonialLoader').style.display = 'none';
        $('#testimonialLoadMore').style.display = 'block';
      } finally {
        loading = false;
      }
    }

    document.getElementById('loadMoreBtn')?.addEventListener('click', () => loadTestimonials());
    document.getElementById('themeToggle')?.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
    document.getElementById('menuToggle')?.addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.toggle('hidden');
    });

    loadStoreName();
    loadTestimonials();
  </script>
</body>
</html>
