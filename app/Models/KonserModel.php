<?php

namespace App\Models;

use CodeIgniter\Model;

class KonserModel extends Model
{
    protected $table            = 'konser';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    
    // Ini adalah kolom-kolom yang boleh diisi datanya
    protected $allowedFields    = ['nama_konser', 'harga', 'poster_url', 'created_at'];
}