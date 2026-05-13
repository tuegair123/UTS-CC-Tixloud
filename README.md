PANDUAN INSTALASI DAN PENGGUNAAN PLATFORM TIXCLOUD
TEGAR WIBISONO (32602400102)
UTS CLOUD COMPUTING
A.	Kebutuhan Perangkat Lunak (Prerequisites) Pastikan perangkat lunak berikut sudah terinstal dan berjalan di komputer:
1.	Docker Desktop (Pastikan mesin Docker sudah dalam keadaan running).
2.	Browser (Google Chrome / Firefox / Edge dll).
3.	Terminal / PowerShell.
B.	Langkah Membuka Aplikasi (Memulai Sesi Baru) Karena aplikasi ini mengusung konsep Ephemeral Cloud Mocking, setiap sesi akan dimulai dari keadaan bersih (fresh install).
1.	Buka Terminal atau PowerShell, lalu arahkan ke dalam folder direktori project TixCloud.
2.	Jalankan perintah berikut untuk membangun dan menyalakan semua kontainer (Nginx, PHP, MySQL, LocalStack) :
docker compose up -d
3.	Penting: Tunggu sekitar 15 - 20 detik. Waktu ini diperlukan agar mesin database MySQL selesai melakukan inisialisasi awal.
4.	Setelah mesin siap, jalankan perintah migrasi ini untuk membangun struktur tabel database :
docker exec -it tixcloud-app php spark migrate
(Tunggu hingga muncul pesan sukses/Done! di terminal).
5.	Selanjutnya, pastikan infrastruktur AWS tiruan (S3 dan SQS) sudah siap dengan menjalankan dua perintah ini secara berurutan :
docker exec -it tixcloud-aws awslocal s3 mb s3://poster-konser
docker exec -it tixcloud-aws awslocal sqs create-queue --queue-name antrian-tiket
6.	Aplikasi siap digunakan! Buka browser dan akses URL: http://localhost:8080/konser
C.	Skenario Pengujian (Testing)
1.	Lakukan input data konser beserta unggah gambar poster.
2.	Gambar poster akan dikirimkan ke AWS S3 (LocalStack), dan tautannya akan disimpan di MySQL.
3.	Sebelum mencoba menekan tombol beli, jalankan perintah pada terminal/powershell untuk membuat antrean :
docker exec -it tixcloud-aws awslocal sqs receive-message --queue-url http://localhost:4566/000000000000/antrian-tiket
(Mungkin bisa di split screen menjadi 2, WEB dan Terminal/PowerShell)
4.	Tekan tombol Beli Tiket (Via SQS) untuk menguji pengiriman pesan antrean (message queuing) ke layanan AWS SQS.
5.	Buka kembali Terminal/Powershell dan jalankan perintah :
docker exec -it tixcloud-aws awslocal sqs receive-message --queue-url http://localhost:4566/000000000000/antrian-tiket
Maka akan muncul pesan JSON berisi data pesanan.
D.	Langkah Menutup Aplikasi (Mengakhiri Sesi & Pembersihan) Setelah selesai melakukan pengujian, lingkungan Docker harus dibersihkan secara total agar tidak meninggalkan data "sampah" (cache memori) untuk pengujian di hari berikutnya.
1.	Buka kembali Terminal/PowerShell yang berada di folder project.
2.	Jalankan perintah berikut :
docker compose down -v
(Flag -v sangat krusial digunakan untuk memastikan seluruh memori/volume bawaan benar-benar dihancurkan). 
3.	Sesi selesai dan aplikasi telah ditutup dengan aman.

KESIMPULAN :
Proyek ini menggunakan LocalStack untuk menerapkan konsep arsitektur Ephemeral Environment. Simulasi difokuskan pada pengujian logika integrasi API Cloud (S3 dan SQS) menggunakan AWS SDK secara lokal, bukan untuk pengujian daya tahan data (Data Durability). Oleh karena itu, infrastruktur akan dikosongkan setiap sesi diakhiri, namun aplikasi sudah berstatus Cloud-Ready dan siap di-deploy ke environment AWS produksi tanpa perlu merombak source code.
KETERBATASAN :
•	Tidak Ada Data Durability (Persistensi Ephemeral): Ini adalah keterbatasan terbesar (dan alasan mengapa gambarmu sempat hilang). AWS S3 asli menjamin data tidak akan hilang karena direplikasi ke berbagai zona ketersediaan (Multi-AZ). Sementara LocalStack versi komunitas adalah layanan stateless/ephemeral; ia berjalan di atas RAM/memori sementara kontainer. Begitu mesin mati, infrastruktur awannya lenyap dan harus diinisialisasi ulang dari nol.
•	Ketiadaan Validasi Keamanan (IAM Policies): AWS sangat ketat dengan Identity and Access Management (IAM). Di LocalStack, kamu bisa menggunakan key sembarangan (seperti test dan test) dan sistem akan tetap mengizinkan akses. Ini membuat pengujian skenario keamanan/otorisasi cloud tidak bisa dilakukan secara akurat di lokal.
•	Keterbatasan Skalabilitas (Resource-Bound): Konsep utama cloud adalah auto-scaling (sumber daya tidak terbatas). Dalam simulasi ini, batas komputasi "awan" kamu dibatasi oleh spesifikasi CPU dan RAM laptopmu sendiri.

TERSIMULASIKAN :
•	Amazon S3 (Simple Storage Service) - Object Storage:
o	Penerapan: Digunakan untuk menyimpan file tidak terstruktur (poster konser).
o	Konsep Cloud: Memisahkan beban penyimpanan file statis dari server utama (Nginx/PHP). Di dunia nyata, ini mengurangi beban bandwidth server aplikasi karena user akan mengunduh gambar langsung dari S3.
•	Amazon SQS (Simple Queue Service) - Message Queuing:
o	Penerapan: Menampung antrean pesanan tiket yang dikirim oleh pengguna saat menekan tombol "Beli Tiket".
o	Konsep Cloud: Menerapkan arsitektur Decoupling (pemisahan tugas) dan Asynchronous Processing. Aplikasi CodeIgniter tidak perlu memproses tiket secara real-time yang bisa membuat web lemot. Ia hanya melempar "pesan" ke SQS, lalu ada worker lain di latar belakang yang memproses antrean tersebut.


