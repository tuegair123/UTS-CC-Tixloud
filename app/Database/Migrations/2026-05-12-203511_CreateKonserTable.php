<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKonserTable extends Migration
{
    public function up()
{
    $this->forge->addField([
        'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'nama_konser' => ['type' => 'VARCHAR', 'constraint' => '255'],
        'harga'       => ['type' => 'INT', 'constraint' => 11],
        'poster_url'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        'created_at'  => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('konser');
}

    public function down()
    {
        //
    }
}
