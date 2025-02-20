<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

     
        $client = User::create([
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
        ]);

    
        $commercial = User::create([
            'name' => 'Commercial Test',
            'email' => 'commercial@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
