<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Attribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Attribute::create(['name' => 'Цвет', 'type' => 'color']);
        Attribute::create(['name' => 'Размер', 'type' => 'size']);
    }
}
