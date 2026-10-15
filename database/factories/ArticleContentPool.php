<?php

namespace Database\Factories;

/**
 * Pustaka contoh artikel berbahasa Indonesia.
 *
 * Dipakai ArticleSeeder supaya artikel yang di-seed relevan dengan
 * kategorinya, bukan teks placeholder dari faker.
 *
 * Seluruh teks memakai karakter ASCII agar bebas dari masalah encoding.
 */
class ArticleContentPool
{
    /**
     * @return array<string, array<int, array{title: string, excerpt: string, paragraphs: list<string>}>>
     */
    public static function articles(): array
    {
        return [
            'teknologi' => [
                [
                    'title' => 'Belajar Docker dari Nol: Menjalankan Laravel Tanpa XAMPP',
                    'excerpt' => 'Cara menjalankan aplikasi Laravel di dalam container, lengkap dengan MySQL dan phpMyAdmin, tanpa memasang XAMPP di laptop.',
                    'paragraphs' => [
                        'Belajar pemrograman web hampir selalu dimulai dengan memasang XAMPP atau Laragon. PHP, database, dan web server diletakkan berderet di satu komputer. Cara itu cepat untuk coba-coba, tetapi cepat juga bermasalah saat proyek pindah mesin.',
                        'Docker memberi pendekatan yang berbeda. Aplikasi, database, dan alat pendukung lainnya masing-masing berjalan di dalam container yang terisolasi. Yang diperlukan hanya satu perintah untuk menyalakan semuanya.',
                        'Untuk proyek Laravel, konfigurasinya cukup sederhana. Satu service untuk aplikasi, satu untuk MySQL, dan satu untuk phpMyAdmin. Ketiganya dihubungkan lewat jaringan internal Docker.',
                        'Bagian yang perlu diperhatikan adalah volume. Folder proyek bisa dipasang langsung ke dalam container, sehingga perubahan kode langsung terlihat tanpa build ulang. Karena itu DocumentRoot diarahkan ke folder public.',
                    ],
                ],
                [
                    'title' => 'Volume dan Network: Mengapa Data Tidak Hilang Saat Container Dihapus',
                    'excerpt' => 'Dua konsep sederhana ini membuat database tetap utuh antar restart, dan antar service tetap bisa saling menjangkau.',
                    'paragraphs' => [
                        'Secara default, data yang ditulis di dalam container akan hilang ketika container dihapus. Untuk database, itu jelas masalah karena seluruh tabel ikut hilang.',
                        'Volume menyimpan data di luar container. Ketika container dihentikan lalu dibuat ulang, volume tetap menempel sehingga databasenya utuh seperti sebelumnya.',
                        'Network punya peran yang berbeda. Container dalam satu jaringan saling mengenali berdasarkan nama service. Karena itu konfigurasi aplikasi menuliskan nama service sebagai host.',
                        'Urutan startup juga penting. Aplikasi hampir selalu membutuhkan database siap sebelum dipakai, jadi pemeriksaan kesehatan pada service database jauh lebih baik daripada menebak-nebak.',
                    ],
                ],
                [
                    'title' => 'Migrasi Database dengan Aman Sebelum Menjalankan migrate:fresh',
                    'excerpt' => 'Perintah migrate:fresh sangat berguna saat pengembangan, tetapi menghapus semua data. Berikut cara memakainya tanpa risiko.',
                    'paragraphs' => [
                        'Dalam Laravel, migrate:fresh menghapus seluruh tabel lalu menjalankan ulang semua migrasi dari awal. Perintah ini jauh lebih cepat daripada rollback satu per satu.',
                        'Masalahnya, perintah ini menghapus semua data. Tidak sengaja menjalankannya di lingkungan yang salah bisa membuat data lama hilang tanpa bisa dikembalikan.',
                        'Karena itu, biasakan memeriksa nama koneksi dan nama database sebelum menekan enter. Pastikan APP_ENV bernilai local dan konfigurasi database menunjuk ke container pengembangan.',
                        'Untuk data contoh, seeding adalah teman terbaik. Dengan seeder yang mengisi tabel kategori, artikel, dan komentar, Anda selalu punya data siap pakai setiap kali database dibangun ulang.',
                    ],
                ],
            ],

            'gaya-hidup' => [
                [
                    'title' => 'Membangun Kebiasaan Produktif Tanpa Burnout',
                    'excerpt' => 'Produktivitas yang bertahan lama bukan soal bekerja lebih keras, tetapi merancang rutinitas yang tidak menguras diri sendiri.',
                    'paragraphs' => [
                        'Banyak orang memulai dengan semangat tinggi, menentukan target besar, lalu berhenti di minggu kedua. Penyebabnya biasanya rutinitas itu dari awal memang tidak realistis.',
                        'Mulailah dari yang kecil. Satu tugas kecil setiap hari yang benar-benar selesai jauh lebih berharga daripada rencana besar yang selalu tertunda.',
                        'Bagikan rencana pada orang lain. Kemungkinan berhasil lebih tinggi ketika ada seseorang yang tahu dan menanyakan progres secara berkala.',
                        'Jaga juga ruang untuk pemulihan. Waktu luang bukan musuh produktivitas, melainkan bahan bakarnya. Orang yang bekerja tanpa jeda akan cepat habis energi.',
                    ],
                ],
                [
                    'title' => 'Menyusun Rutinitas Pagi yang Masuk Akal',
                    'excerpt' => 'Kebiasaan pagi tidak harus dramatis. Beberapa perubahan kecil yang realistis sudah cukup memperbaiki rasa segar dan fokus sepanjang hari.',
                    'paragraphs' => [
                        'Mitos rutinitas pagi sering tidak realistis. Bangun jam lima, mandi air dingin, lalu menulis jurnal. Kenyataannya sebagian besar orang akan kembali tidur setelah dua hari.',
                        'Yang lebih masuk akal adalah memulai dari satu kebiasaan kecil dan memaksakannya konsisten selama dua minggu. Minum segelas air, berjalan kaki singkat, atau duduk tenang sejenak.',
                        'Waktu tidur jauh lebih menentukan daripada waktu bangun. Bangun dini tanpa tidur cukup hanya menghasilkan rasa lelah sepanjang hari.',
                        'Hindari media sosial sebelum pagi berlalu. Membaca berita sejak pagi membuat pikiran berputar hal yang sama sebelum sempat tenang.',
                    ],
                ],
                [
                    'title' => 'Mengelola Waktu Layar dan Menjaga Fokus',
                    'excerpt' => 'Notifikasi mengambil perhatian tanpa suara. Beberapa aturan sederhana bisa mengurangi gangguan itu secara nyata.',
                    'paragraphs' => [
                        'Yang mahal bukan notifikasinya, melainkan perpindahan perhatian. Setiap kali membuka notifikasi, otak butuh beberapa menit untuk kembali ke tugas yang sedang dikerjakan.',
                        'Mulailah dari satu langkah sederhana: matikan notifikasi di perangkat yang bukan telepon, lalu letakkan HP di ruangan lain saat bekerja.',
                        'Gunakan aturan tiga puluh menit. Kerjakan satu hal selama tiga puluh menit tanpa gangguan sebelum mengizinkan diri memeriksa pesan.',
                        'Fokus bukan bakat, melainkan hasil kebiasaan yang dijaga. Mulailah dari satu gangguan yang paling mengganggu, hilangkan, dan lihat perbedaannya sendiri.',
                    ],
                ],
            ],

            'perjalanan' => [
                [
                    'title' => 'Merencanakan Perjalanan Murah dengan Anggaran yang Masuk Akal',
                    'excerpt' => 'Liburan tidak harus mahal. Dengan perencanaan yang cermat, biaya perjalanan bisa ditekan tanpa mengurangi pengalaman.',
                    'paragraphs' => [
                        'Perjalanan murah bukan soal memangkas semua pengeluaran, tetapi memilih dengan sadar bagian mana yang layak dan bagian mana yang bisa diabaikan.',
                        'Tentukan anggaran total lebih dulu, lalu pecah menjadi biaya transportasi, tempat menginap, makan, dan cadangan. Pecahan ini membuat pengeluaran mudah dipantau.',
                        'Waktu bepergian paling mahal di perjalanan. Mengheimati hari kerja biasanya membuat harga tiket dan sewa tempat jauh lebih murah.',
                        'Bawa air minum dan makanan ringan sendiri. Penghematan kecil seperti ini sering terasa lebih besar dibanding memangkas satu pengeluaran besar.',
                    ],
                ],
                [
                    'title' => 'Menyusun Itinerary yang Tidak Terlalu Padat',
                    'excerpt' => 'Rencana yang terlalu padat membuat liburan terasa melelahkan. Menyusun itinerary dengan ritme yang longgar membuat perjalanan lebih berarti.',
                    'paragraphs' => [
                        'Banyak itinerary dibuat dengan memaksakan semua tempat dalam sehari. Hasilnya, liburan terlihat dari daftar yang panjang, bukan dari pengalaman yang benar-benar dirasakan.',
                        'Batasi dua lokasi utama per hari sudah cukup. Sisanya bisa dipakai untuk bersantai, jalan-jalan, atau beristirahat tanpa rencana.',
                        'Tetapkan waktu untuk makan dan istirahat di dalam rencana, bukan sekadar jeda di sela tempat. Tanpa itu, waktu tersebut akan habis untuk berjalan atau mencari hal kecil.',
                        'Sisakan satu hari tanpa kegiatan apa pun. Banyak perjalanan yang paling diingat justru berasal dari hari yang paling kosong.',
                    ],
                ],
                [
                    'title' => 'Menata Isi Tas agar Nyaman Sepanjang Perjalanan',
                    'excerpt' => 'Barang terlalu banyak membuat bahu terasa berat. Beberapa langkah sederhana bisa membuat tas jauh lebih ringkas.',
                    'paragraphs' => [
                        'Banyak orang membawa terlalu banyak karena takut kekurangan. Padahal setiap barang tambahan punya biaya, baik berupa ruang maupun beban yang dibawa sepanjang hari.',
                        'Mulai dari daftar pendek: pakaian sesuai jumlah hari, perlengkapan mandi, pengisi daya, dan obat pribadi. Tambahkan sisanya hanya jika benar-benar diperlukan.',
                        'Gunakan pakaian yang mudah dipadu agar jumlah barang bisa dikurangi. Ini menghemat ruang sekaligus membuat mencampur pakaian lebih mudah saat di jalan.',
                        'Letakkan barang berat paling dekat dengan punggung. Cara ini mengurangi beban pada bahu dan membuat perjalanan jauh lebih nyaman.',
                    ],
                ],
            ],

            'makanan' => [
                [
                    'title' => 'Merancang Resep Sederhana yang Mudah Diulang',
                    'excerpt' => 'Resep yang enak tidak selalu rumit. Beberapa trik dasar membuat makanan rumahan lebih konsisten hasilnya.',
                    'paragraphs' => [
                        'Resep yang baik bukan yang paling rumit, tapi yang hasilnya konsisten. Konsistensi tercapai dengan mengukur bahan dan menjaga urutan langkah tetap sama setiap kali memasak.',
                        'Siapkan bahan lebih dulu sebelum kompor menyala. Langkah ini sering dianggap remeh, tapi justru di sinilah sebagian besar kesalahan terjadi.',
                        'Cicipi di tengah proses dan sesuaikan rasanya. Sedikit penyesuaian di tahap ini jauh lebih mudah daripada memperbaiki hidangan yang sudah matang sepenuhnya.',
                        'Catat tambahan yang berhasil, lalu tulis ulang resepnya. Resep yang selalu disimpan adalah cara paling murah untuk memasak cepat di kemudian hari.',
                    ],
                ],
                [
                    'title' => 'Menyimpan Bahan Mentah agar Tidak Terbuang',
                    'excerpt' => 'Sayur dan bahan lain sering terbuang karena salah cara menyimpan. Beberapa trik sederhana bisa memperpanjang umur bahan.',
                    'paragraphs' => [
                        'Banyak bahan terbuang bukan karena buruknya, tetapi karena cara menyimpannya keliru. Menyimpan dengan benar bisa menghemat belanjaan mingguan.',
                        'Sayuran berdaun lebih lama disimpan di kulkas pada suhu yang sesuai dan di dalam wadah yang kedap lembap. Buang bagian yang sudah layu sebelum disimpan.',
                        'Untuk bahan protein, pisahkan porsi sejak awal dengan memisahkan bagian yang akan dimakan minggu ini dalam wadah kecil. Sisanya bisa langsung dibekukan.',
                        'Buah yang matang cepat umumnya lebih baik disimpan di luar kulkas pada suhu ruang dan dimakan dalam beberapa hari, bukan di dalam kulkas.',
                    ],
                ],
                [
                    'title' => 'Makan Sederhana Tanpa Terasa Bosan',
                    'excerpt' => 'Makan rumahan tidak harus terasa sama setiap hari. Variasinya ada pada cara masak, bukan pada bahan yang mahal.',
                    'paragraphs' => [
                        'Makanan rumahan sering terasa membosankan bukan karena bahannya sederhana, tetapi karena penyajiannya selalu sama.',
                        'Variasikan cara masak satu bahan yang sama, bukan bahannya. Tomat bisa di sup, ditumis, atau dibuat salad segar dengan daun sebagai pelengkap.',
                        'Simpan sebagian kaldu di kulkas untuk dipakai saat hari sibuk, sehingga Anda lebih sering masak di rumah.',
                        'Yang terakhir, perbanyak variasi dari waktu ke waktu. Pilihan yang luas membuat rasa di lidah tidak cepat bosan.',
                    ],
                ],
            ],

            'kesehatan' => [
                [
                    'title' => 'Mulai Berjalan Kaki Tanpa Alat',
                    'excerpt' => 'Berjalan kaki adalah bentuk olahraga paling mudah dimulai, dan manfaatnya nyata untuk tubuh maupun pikiran.',
                    'paragraphs' => [
                        'Berjalan kaki termasuk olahraga dengan hambatan paling rendah. Tidak perlu alat, tidak perlu ke gym, dan bisa dilakukan di dekat rumah.',
                        'Mulailah dari durasi pendek lalu tambah perlahan. Target yang realistis adalah dua puluh menit per hari selama tiga kali seminggu, bukan langsung satu jam.',
                        'Kebiasaan berjalan juga membantu pemulihan setelah seharian kerja, karena tubuh bergerak setelah lama diam.',
                        'Sepatu yang nyaman adalah investasi paling murah untuk konsistensi. Sepatu yang menyakitkan membuat niat berjalan hilang dalam dua hari.',
                    ],
                ],
                [
                    'title' => 'Tidur Cukup: Fondasi yang Sering Diabaikan',
                    'excerpt' => 'Tidur cukup dipengaruhi banyak hal kecil yang bisa diatur, dan dampaknya terhadap kesehatan sangat besar.',
                    'paragraphs' => [
                        'Tidur adalah kebutuhan yang sering dikorbankan pertama kali saat pekerjaan sedang padat. Dampaknya ke konsentrasi dan kesehatan sangat nyata.',
                        'Coba jaga jam tidur dan jam bangun tetap, termasuk saat akhir pekan. Konsistensi waktu lebih berpengaruh daripada jumlah jamnya.',
                        'Kurangi cahaya layar satu jam sebelum tidur dan redupkan lampu di kamar. Sinyal seperti ini membantu tubuh berpindah ke mode istirahat.',
                        'Kalau sulit tidur, redupkan sumber cahaya dan hindari kafein setelah jam makan siang. Hal kecil ini sering lebih efektif daripada mencoba berbagai cara lain.',
                    ],
                ],
                [
                    'title' => 'Makan dengan Teratur dan Seimbang',
                    'excerpt' => 'Pola makan yang sederhana bisa dimulai dengan cara yang jelas: makan secara teratur, perhatikan porsi, dan jangan melewatkan jam makan.',
                    'paragraphs' => [
                        'Makan teratur lebih penting daripada memilih menu yang rumit. Tubuh jauh lebih stabil waktu-nya yang teratur.',
                        'Perhatikan porsi, bukan jenis makanan yang dihindari. Mengurangi porsi jam makan lebih efektif daripada mengganti semua bahan sekaligus.',
                        'Minum air putih cukup sepanjang hari, dan ganti cemilan tinggi gula dengan potongan buah.',
                        'Perubahan kecil yang konsisten menghasilkan hasil yang jauh lebih besar daripada diet ketat yang hanya bertahan dua minggu.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Imbuhan judul supaya beberapa artikel dengan topik sama tetap punya
     * judul yang berbeda.
     *
     * Jumlah imbuhan (5) sengaja dibuat prima relatif jumlah artikel dasar
     * per kategori (3) supaya kombinasi keduanya tidak pernah berulang
     * untuk urutan 15 artikel pertama.
     *
     * @return list<string>
     */
    public static function titleSuffixes(): array
    {
        return [
            ' untuk Pemula',
            ' yang Perlu Diketahui',
            ': Panduan Praktis',
            ' Versi Singkat',
            ' bagi Pembaca Baru',
        ];
    }

    /**
     * Semua artikel untuk satu kategori.
     *
     * @return array<int, array{title: string, excerpt: string, paragraphs: list<string>}>
     */
    public static function forCategory(?string $categorySlug = null): array
    {
        $all = self::articles();

        if ($categorySlug !== null && isset($all[$categorySlug])) {
            return $all[$categorySlug];
        }

        return array_merge(...array_values($all));
    }

    /**
     * Ambil satu artikel acak dari pustaka.
     *
     * Kalau kategori yang diminta tidak ada di pustaka, memakai artikel
     * kategori teknologi sebagai cadangan supaya seeding tidak pernah gagal.
     *
     * @return array{title: string, excerpt: string, paragraphs: list<string>}
     */
    public static function pick(?string $categorySlug = null): array
    {
        $pool = self::forCategory($categorySlug);

        return $pool[random_int(0, count($pool) - 1)];
    }

    /**
     * Ambil artikel berdasarkan urutan, bukan acak, supaya hasilnya stabil.
     *
     * @return array{title: string, excerpt: string, paragraphs: list<string>}
     */
    public static function pickAt(?string $categorySlug, int $index): array
    {
        $pool = self::forCategory($categorySlug);

        return $pool[$index % count($pool)];
    }

    /**
     * Nama penulis komentar berbahasa Indonesia.
     *
     * @return list<string>
     */
    public static function commentAuthors(): array
    {
        return [
            'Budi Santoso',
            'Siti Rahayu',
            'Andi Wijaya',
            'Dewi Lestari',
            'Rudi Hartono',
            'Nurul Hidayah',
            'Agus Setiawan',
            'Rina Marlina',
            'Fajar Nugroho',
            'Intan Permata',
            'Bayu Kurniawan',
            'Maya Puspita',
        ];
    }

    /**
     * Isi komentar berbahasa Indonesia.
     *
     * @return list<string>
     */
    public static function commentSentences(): array
    {
        return [
            'Artikel ini sangat membantu, terima kasih sudah dijelaskan dengan detail.',
            'Akhirnya ada penjelasan yang mudah dipahami. Sudah saya coba dan berhasil.',
            'Tolong dibuatkan artikel lanjutannya ya, karena bagian ini masih membingungkan.',
            'Bagian contoh kodenya langsung saya coba dan berjalan dengan baik.',
            'Contoh penerapannya persis seperti yang saya cari, terima kasih banyak.',
            'Sedikit tambahan: kalian bisa langsung memakai ini di proyek masing-masing.',
            'Terima kasih sudah berbagi pengalaman, sangat membantu.',
            'Penjelasannya runtut dan mudah dipahami dari awal sampai akhir.',
            'Ini yang saya butuhkan. Semoga ada artikel serupa untuk topik lain.',
            'Sudah saya coba tapi hasilnya sedikit berbeda, mungkin karena versi berbeda.',
            'Komentar ini relevan sekali, tapi tetap membantu untuk saling berbagi.',
            'Saya jadi lebih paham bedanya volume dengan bind mount.',
            'Penjelasan tentang healthcheck service sangat membantu, akhirnya paham urutannya.',
            'Saya coba di laptop sendiri dan hasilnya konsisten. Mantap.',
            'Apakah ada penjelasan kenapa volume lebih aman daripada bind mount di Windows?',
            'Bagian soal network sudah saya pahami, tapi masih bingung soal port mapping.',
            'Sudah saya terapkan di proyek sekolah saya dan berhasil tanpa masalah.',
            'Contoh kodenya jelas sekali, tapi Desire penjelasan untuk kasus yang lebih rumit.',
            'Terima kasih sudah dijelaskan hal yang tadinya membuat saya bingung.',
            'Ini salah satu artikel terbaik yang pernah saya baca di sini.',
            'Bisa dijelaskan juga cara menangani database yang sudah berjalan lama?',
            'Sudah saya coba di lokal dan jalan, tapi belum sempat coba di server sungguhan.',
            'Ternyata penyebabnya sepele, saya mencari lama sekali. Terima kasih banyak.',
            'Menarik, selama ini saya kira volume dan bind mount itu sama saja.',
            'Saya setuju dengan poin soal urutan start up, ini yang sering bikin error.',
            'Sukses! Build pertama langsung jalan tanpa error sama sekali.',
            'Mohon versi untuk Windows juga ya, soalnya saya pakai di rumah.',
            'Kontennya padat tapi mudah dipahami, tidak perlu mengulang-ulang baca.',
            'Saya bookmark dulu, nanti akan dipakai lagi saat lupa.',
        ];
    }
}
