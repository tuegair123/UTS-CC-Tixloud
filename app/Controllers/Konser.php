<?php

namespace App\Controllers;

use App\Models\KonserModel;
use Aws\S3\S3Client;
use CodeIgniter\Controller;
use Aws\Sqs\SqsClient;

class Konser extends BaseController
{
    private $s3;

    public function __construct()
    {
        // Menghubungkan CI4 ke AWS S3 (LocalStack)
        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => getenv('AWS_DEFAULT_REGION'),
            'endpoint'    => getenv('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true, 
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    public function index()
    {
        $model = new KonserModel();
        $data['konser'] = $model->findAll();
        
        return view('konser_view', $data);
    }

    public function upload()
    {
        // 1. TAMBAHKAN BLOK VALIDASI DI SINI 
        // max_size[poster,10240] artinya maksimal 10MB (10240 KB)
        $aturanValidasi = [
            'poster' => 'uploaded[poster]|max_size[poster,10240]|is_image[poster]'
        ];

        if (!$this->validate($aturanValidasi)) {
            // Jika file terlalu besar, kembalikan ke halaman sebelumnya
            // (Opsional: Kamu bisa tambahkan alert error di view nanti)
            return redirect()->to('/konser');
        }

        // 2. KODE ASLIMU DIMULAI DARI SINI BAWAH SINI
        // Ambil data dari form
        $file = $this->request->getFile('poster');
        $namaKonser = $this->request->getPost('nama_konser');
        $harga = $this->request->getPost('harga');

        if ($file->isValid() && !$file->hasMoved()) {
            $namaFileRandom = $file->getRandomName();
            $key = 'posters/' . $namaFileRandom;

            // UPLOAD FILE KE AWS S3 BUCKET
            $this->s3->putObject([
                'Bucket' => 'poster-konser', // Nama bucket yang kita buat di awal
                'Key'    => $key,
                'Body'   => fopen($file->getTempName(), 'rb'),
                'ACL'    => 'public-read',
            ]);

            // Simpan URL gambar ke Database MySQL
            $urlS3 = 'http://localhost:4566/poster-konser/' . $key;
            
            $model = new KonserModel();
            $model->save([
                'nama_konser' => $namaKonser,
                'harga'       => $harga,
                'poster_url'  => $urlS3,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->to('/konser');
    }

    public function beli($id)
    {
        // 1. Inisialisasi AWS SQS Client
        $sqs = new SqsClient([
            'version'     => 'latest',
            'region'      => getenv('AWS_DEFAULT_REGION'),
            'endpoint'    => getenv('AWS_ENDPOINT'),
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // 2. Siapkan data pesanan (Simulasi)
        $pesanan = [
            'konser_id' => $id,
            'user_id'   => rand(100, 999), // Anggap saja ID pembeli acak
            'waktu'     => date('Y-m-d H:i:s'),
            'status'    => 'Menunggu diproses'
        ];

        // 3. Dapatkan URL Antrean yang kita buat di terminal tadi
        $queueUrlResult = $sqs->getQueueUrl([
            'QueueName' => 'antrian-tiket'
        ]);
        $queueUrl = $queueUrlResult->get('QueueUrl');

        // 4. Kirim data pesanan ke dalam Antrean SQS
        $sqs->sendMessage([
            'QueueUrl'    => $queueUrl,
            'MessageBody' => json_encode($pesanan)
        ]);

        // 5. Beri pesan sukses ke halaman depan
        session()->setFlashdata('sukses_beli', 'Pesanan masuk ke antrean AWS SQS! Sedang diproses di latar belakang.');
        
        return redirect()->to('/konser');
    }
}