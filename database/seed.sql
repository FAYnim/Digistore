-- ============================================================
-- Seed Data: digital_store
-- Data dummy untuk development / testing dashboard
-- ============================================================

USE digital_store;

-- ------------------------------------------------------------
-- admin_users
-- Password: admin123  (hashed dengan password_hash)
-- ------------------------------------------------------------
INSERT INTO admin_users (username, password, name, status) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'active');

-- ------------------------------------------------------------
-- categories
-- ------------------------------------------------------------
INSERT INTO categories (name, slug, icon, status, sort_order) VALUES
('Akun Premium',    'akun-premium',     'fa-solid fa-crown',               'active', 1),
('Source Code',     'source-code',      'fa-solid fa-code',                'active', 2),
('Template Website','template-website', 'fa-solid fa-window-maximize',     'active', 3),
('Tools AI',        'tools-ai',         'fa-solid fa-wand-magic-sparkles', 'active', 4),
('Desain Digital',  'desain-digital',   'fa-solid fa-palette',             'inactive', 5),
('Produktivitas',   'produktivitas',    'fa-solid fa-bolt',                'active', 6);

-- ------------------------------------------------------------
-- products
-- ------------------------------------------------------------
INSERT INTO products
  (category_id, name, slug, description, price, original_price, stock, image_url, badge, status, is_featured, sold_count, rating)
VALUES
(4, 'Google AI Pro 12 Bulan',   'google-ai-pro-12-bulan',   'Akses tools AI premium siap pakai. Optimal untuk produktivitas harian.',            25000,  50000, 12, 'https://placehold.co/600x400/6366f1/ffffff?text=Google+AI+Pro',   'Best Seller',  'active', 1, 320, 4.9),
(1, 'ChatGPT Plus Sharing',     'chatgpt-plus-sharing',     'Akun sharing premium untuk produktivitas maksimal.',                                  45000,  75000,  8, 'https://placehold.co/600x400/10b981/ffffff?text=ChatGPT+Plus',    'Popular',      'active', 1, 210, 4.8),
(1, 'Netflix Premium 1 Bulan',  'netflix-premium-1-bulan',  'Akun hiburan premium aktif cepat. Bisa dipakai langsung.',                            35000,  60000,  4, 'https://placehold.co/600x400/ef4444/ffffff?text=Netflix',          'Limited',      'active', 1, 185, 4.7),
(1, 'Canva Pro Edu',            'canva-pro-edu',             'Desain cepat dengan akses Canva Pro penuh.',                                          20000,  40000,  0, 'https://placehold.co/600x400/f59e0b/ffffff?text=Canva+Pro',        'Hemat',        'out_of_stock', 0, 98, 4.6),
(2, 'Source Code Toko Online',  'source-code-toko-online',  'Template toko online siap modifikasi, teknologi modern.',                             99000, 150000, 20, 'https://placehold.co/600x400/3b82f6/ffffff?text=Source+Code',     'Recommended',  'active', 0, 55, 4.5),
(3, 'Template Landing SaaS',    'template-landing-saas',    'Landing page modern untuk produk digital, desain premium.',                            79000, 120000, 14, 'https://placehold.co/600x400/8b5cf6/ffffff?text=Landing+SaaS',    'New',          'draft',  0, 12, 4.3),
(4, 'Midjourney 1 Bulan',       'midjourney-1-bulan',       'Akses Midjourney untuk generate gambar AI berkualitas tinggi.',                        55000,  90000,  6, 'https://placehold.co/600x400/ec4899/ffffff?text=Midjourney',       'Trending',     'active', 1, 143, 4.8),
(6, 'Notion Pro Template',      'notion-pro-template',      'Template Notion lengkap untuk manajemen proyek dan produktivitas.',                    15000,  30000, 50, 'https://placehold.co/600x400/14b8a6/ffffff?text=Notion+Pro',      'Hemat',        'active', 0, 76, 4.4);

-- ------------------------------------------------------------
-- orders
-- ------------------------------------------------------------
INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, total_amount, payment_method, status, note) VALUES
('ORD-20260624-001', 'Raka Pratama',  'raka@mail.test',   '6281234567890', 25000, 'QRIS',     'paid',      NULL),
('ORD-20260624-002', 'Nadia Putri',   'nadia@mail.test',  '6281234567891', 45000, 'Transfer', 'pending',   NULL),
('ORD-20260623-003', 'Dimas Arya',    'dimas@mail.test',  '6281234567892', 35000, 'E-Wallet', 'completed', NULL),
('ORD-20260623-004', 'Sari Dewi',     'sari@mail.test',   '6281234567893', 55000, 'QRIS',     'paid',      'Tolong kirim cepat'),
('ORD-20260622-005', 'Budi Santoso',  'budi@mail.test',   '6281234567894', 20000, 'Transfer', 'cancelled', 'Dibatalkan pembeli'),
('ORD-20260622-006', 'Lina Susanti',  'lina@mail.test',   '6281234567895', 79000, 'QRIS',     'completed', NULL),
('ORD-20260621-007', 'Eko Prasetyo',  'eko@mail.test',    '6281234567896', 99000, 'Transfer', 'paid',      NULL);

-- ------------------------------------------------------------
-- order_items
-- ------------------------------------------------------------
INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES
(1, 1, 'Google AI Pro 12 Bulan',  1, 25000, 25000),
(2, 2, 'ChatGPT Plus Sharing',    1, 45000, 45000),
(3, 3, 'Netflix Premium 1 Bulan', 1, 35000, 35000),
(4, 7, 'Midjourney 1 Bulan',      1, 55000, 55000),
(5, 4, 'Canva Pro Edu',           1, 20000, 20000),
(6, 6, 'Template Landing SaaS',   1, 79000, 79000),
(7, 5, 'Source Code Toko Online', 1, 99000, 99000);

-- ------------------------------------------------------------
-- testimonials
-- ------------------------------------------------------------
INSERT INTO testimonials (name, role, message, rating, status) VALUES
('Raka Pratama', 'Mahasiswa',         'Produk cepat dikirim dan aman digunakan. Sangat rekomendasikan!',              5, 'visible'),
('Nadia Putri',  'Freelancer',        'Harga sangat cocok, admin responsif banget. Sudah order berkali-kali.',         5, 'visible'),
('Dimas Arya',   'Content Creator',   'Katalog produk mudah dipakai, pilihan lengkap. Mantap!',                        4, 'hidden'),
('Sari Dewi',    'Guru',              'Akun Canva Pro sangat membantu pekerjaan saya sehari-hari.',                    5, 'visible'),
('Budi Santoso', 'Programmer',        'Source code kualitas premium, dokumentasi jelas. Worth it!',                   5, 'visible'),
('Lina Susanti', 'Designer',          'Template websitenya bagus sekali, mudah dikustomisasi.',                        4, 'visible');

-- ------------------------------------------------------------
-- store_settings
-- ------------------------------------------------------------
INSERT INTO store_settings (setting_key, setting_value) VALUES
('store_name',        'DigiStore'),
('store_tagline',     'Produk Digital Premium'),
('store_description', 'Katalog produk digital berkualitas tinggi dengan harga terjangkau.'),
('store_whatsapp',    '6281234567890'),
('store_email',       'admin@digistore.test'),
('store_instagram',   'digistore'),
('default_theme',     'light'),
('accent_color',      '#2563EB');
