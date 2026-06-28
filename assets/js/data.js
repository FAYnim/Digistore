const products = [
  { id: 1, name: "Google AI Pro 12 Bulan", category: "Tools AI", price: 25000, originalPrice: 50000, image: "https://placehold.co/600x400/145cff/ffffff?text=Google+AI+Pro", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.9, sold: 320, stock: 12, isFeatured: true, createdAt: "2026-06-01" },
  { id: 2, name: "ChatGPT Plus Sharing", category: "Akun Premium", price: 45000, originalPrice: 75000, image: "https://placehold.co/600x400/101522/ffffff?text=ChatGPT+Plus", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.8, sold: 210, stock: 8, isFeatured: true, createdAt: "2026-06-02" },
  { id: 3, name: "Netflix Premium 1 Bulan", category: "Akun Premium", price: 30000, originalPrice: 55000, image: "https://placehold.co/600x400/d72d2d/ffffff?text=Netflix+Premium", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.7, sold: 185, stock: 5, isFeatured: true, createdAt: "2026-06-03" },
  { id: 4, name: "Source Code Toko Online", category: "Source Code", price: 99000, originalPrice: 150000, image: "https://placehold.co/600x400/07111f/ffffff?text=Source+Code", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.9, sold: 90, stock: 20, isFeatured: true, createdAt: "2026-06-04" },
  { id: 5, name: "Template Landing Page SaaS", category: "Template Website", price: 59000, originalPrice: 100000, image: "https://placehold.co/600x400/159447/ffffff?text=SaaS+Template", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.6, sold: 75, stock: 15, isFeatured: false, createdAt: "2026-06-05" },
  { id: 6, name: "UI Kit Portfolio Developer", category: "Desain Digital", price: 35000, originalPrice: 70000, image: "https://placehold.co/600x400/d89108/ffffff?text=UI+Kit", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.8, sold: 130, stock: 0, isFeatured: false, createdAt: "2026-06-06" },
  { id: 7, name: "Notion Productivity Template", category: "Produktivitas", price: 20000, originalPrice: 40000, image: "https://placehold.co/600x400/687084/ffffff?text=Notion+Template", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.5, sold: 65, stock: 18, isFeatured: false, createdAt: "2026-06-07" },
  { id: 8, name: "AI Prompt Pack Creator", category: "Tools AI", price: 49000, originalPrice: 85000, image: "https://placehold.co/600x400/74a7ff/07111f?text=Prompt+Pack", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.", rating: 4.9, sold: 155, stock: 10, isFeatured: false, createdAt: "2026-06-08" }
];

const categories = [
  { id: "all", name: "Semua Produk", icon: "fa-solid fa-bag-shopping" },
  { id: "akun-premium", name: "Akun Premium", icon: "fa-solid fa-star" },
  { id: "source-code", name: "Source Code", icon: "fa-solid fa-laptop-code" },
  { id: "template-website", name: "Template Website", icon: "fa-solid fa-globe" },
  { id: "tools-ai", name: "Tools AI", icon: "fa-solid fa-robot" },
  { id: "desain-digital", name: "Desain Digital", icon: "fa-solid fa-palette" },
  { id: "produktivitas", name: "Produktivitas", icon: "fa-solid fa-bolt" }
];

// Testimoni disembunyikan sementara
// const testimonials = [
//   { id: 1, name: "Raka Pratama", role: "Mahasiswa", message: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.", rating: 5 },
//   { id: 2, name: "Nadia Putri", role: "Freelancer", message: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.", rating: 5 },
//   { id: 3, name: "Dimas Arya", role: "Content Creator", message: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.", rating: 4 }
// ];
