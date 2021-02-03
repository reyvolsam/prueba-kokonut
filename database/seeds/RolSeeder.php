<?php

use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'id'    => 1, 
            'descripcion' => 'Usuario',
        ]);
        DB::table('roles')->insert([
            'id'    => 2,
            'descripcion' => 'Moderador',
        ]);
    }
}
