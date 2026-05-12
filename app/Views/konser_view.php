<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TixCloud - Aplikasi Tiket Konser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
<?php if(session()->getFlashdata('sukses_beli')): ?>
    <div class="alert alert-success text-center fw-bold shadow-sm">
        ✅ <?= session()->getFlashdata('sukses_beli') ?>
    </div>
<?php endif; ?>
    
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center mb-4">🎵 Tambah Konser Baru</h2>
            <div class="card shadow-sm p-4">
                <!-- Form Upload ke S3 -->
                <form action="/konser/upload" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Konser</label>
                        <input type="text" name="nama_konser" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Tiket (Rp)</label>
                        <input type="number" name="harga" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poster Konser (JPG/PNG)</label>
                        <input type="file" name="poster" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan ke Cloud S3</button>
                </form>
            </div>
        </div>
    </div>

    <hr>
    
    <h3 class="mt-5 mb-4 text-center">Daftar Konser (Image from AWS S3)</h3>
    <div class="row">
        <?php foreach($konser as $k): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <!-- Menampilkan Gambar langsung dari URL S3 -->
                    <img src="<?= $k['poster_url'] ?>" class="card-img-top" alt="Poster" style="height: 350px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= $k['nama_konser'] ?></h5>
                        <p class="card-text text-danger fw-bold">Rp <?= number_format($k['harga'], 0, ',', '.') ?></p>
                        <a href="/konser/beli/<?= $k['id'] ?>" class="btn btn-success w-100">Beli Tiket (Via SQS)</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>