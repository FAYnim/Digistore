const dashboardProducts = [
  { id: 1, name: "Google AI Pro 12 Bulan", category: "Tools AI", price: 25000, originalPrice: 50000, stock: 12, status: "Aktif", badge: "Best Seller", image: "https://placehold.co/600x400?text=Google+AI+Pro", description: "Akses tools AI premium siap pakai.", featured: true },
  { id: 2, name: "ChatGPT Plus Sharing", category: "Akun Premium", price: 45000, originalPrice: 75000, stock: 8, status: "Aktif", badge: "Popular", image: "https://placehold.co/600x400?text=ChatGPT+Plus", description: "Akun sharing premium untuk produktivitas.", featured: true },
  { id: 3, name: "Netflix Premium 1 Bulan", category: "Akun Premium", price: 35000, originalPrice: 60000, stock: 4, status: "Aktif", badge: "Limited", image: "https://placehold.co/600x400?text=Netflix", description: "Akun hiburan premium aktif cepat.", featured: true },
  { id: 4, name: "Canva Pro Edu", category: "Akun Premium", price: 20000, originalPrice: 40000, stock: 0, status: "Habis", badge: "Hemat", image: "https://placehold.co/600x400?text=Canva+Pro", description: "Desain cepat dengan akses Canva Pro.", featured: false },
  { id: 5, name: "Source Code Toko Online", category: "Source Code", price: 99000, originalPrice: 150000, stock: 20, status: "Aktif", badge: "Recommended", image: "https://placehold.co/600x400?text=Source+Code", description: "Template toko online siap modifikasi.", featured: false },
  { id: 6, name: "Template Landing SaaS", category: "Template Website", price: 79000, originalPrice: 120000, stock: 14, status: "Draft", badge: "New", image: "https://placehold.co/600x400?text=Landing+SaaS", description: "Landing page modern untuk produk digital.", featured: false }
];

const dashboardCategories = [
  { id: 1, name: "Akun Premium", slug: "akun-premium", icon: "fa-solid fa-crown", status: "Aktif" },
  { id: 2, name: "Source Code", slug: "source-code", icon: "fa-solid fa-code", status: "Aktif" },
  { id: 3, name: "Template Website", slug: "template-website", icon: "fa-solid fa-window-maximize", status: "Aktif" },
  { id: 4, name: "Tools AI", slug: "tools-ai", icon: "fa-solid fa-wand-magic-sparkles", status: "Aktif" },
  { id: 5, name: "Desain Digital", slug: "desain-digital", icon: "fa-solid fa-palette", status: "Draft" },
  { id: 6, name: "Produktivitas", slug: "produktivitas", icon: "fa-solid fa-bolt", status: "Aktif" }
];

const dashboardOrders = [
  { id: 1, code: "ORD-001", customer: "Raka Pratama", email: "raka@mail.test", phone: "081234567890", product: "Google AI Pro 12 Bulan", total: 25000, method: "QRIS", status: "Dibayar", date: "2026-06-24" },
  { id: 2, code: "ORD-002", customer: "Nadia Putri", email: "nadia@mail.test", phone: "081234567891", product: "ChatGPT Plus Sharing", total: 45000, method: "Transfer", status: "Menunggu", date: "2026-06-24" },
  { id: 3, code: "ORD-003", customer: "Dimas Arya", email: "dimas@mail.test", phone: "081234567892", product: "Netflix Premium 1 Bulan", total: 35000, method: "E-Wallet", status: "Selesai", date: "2026-06-23" }
];

const dashboardTestimonials = [
  { id: 1, name: "Raka Pratama", role: "Mahasiswa", rating: 5, message: "Produk cepat dikirim dan aman.", status: "Tampil" },
  { id: 2, name: "Nadia Putri", role: "Freelancer", rating: 5, message: "Harga cocok, admin responsif.", status: "Tampil" },
  { id: 3, name: "Dimas Arya", role: "Creator", rating: 4, message: "Katalog mudah dipakai.", status: "Sembunyi" }
];
