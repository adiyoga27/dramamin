<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            [
                'name' => 'Dramabox',
                'api_url' => 'https://dramabox.dramabos.my.id/api/v1/',
                'api_key' => '30668AC178A065E6EA49D0CBB822E30F',
                'status' => true,
            ],
            [
                'name' => 'Melolo',
                'api_url' => 'https://melolo.dramabos.my.id/api/',
                'api_key' => '30668AC178A065E6EA49D0CBB822E30F',
                'status' => true,
            ],
            [
                'name' => 'Netshort',
                'api_url' => 'https://netshort.dramabos.my.id/api/',
                'api_key' => '30668AC178A065E6EA49D0CBB822E30F',
                'status' => true,
            ],
            [
                'name' => 'Reellife',
                'api_url' => 'https://reelife.dramabos.my.id/api/v1/',
                'api_key' => '30668AC178A065E6EA49D0CBB822E30F',
                'status' => true,
            ],
        ];

        foreach ($resources as $res) {
            \App\Models\Resource::updateOrCreate(
                ['name' => $res['name']],
                $res
            );
        }

        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
    }
}
