<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'จัดการสำนัก',
                'slug' => 'manage-sources',
                'route_name' => 'backoffice.sources.*',
                'description' => 'สามารถจัดการข้อมูลสำนักหวยได้',
                'order' => 1,
            ],
            [
                'name' => 'จัดการผลหวย',
                'slug' => 'manage-results',
                'route_name' => 'backoffice.results.*',
                'description' => 'สามารถจัดการผลหวยได้',
                'order' => 2,
            ],
            [
                'name' => 'จัดการเลขสำนัก',
                'slug' => 'manage-numbers',
                'route_name' => 'backoffice.numbers.*',
                'description' => 'สามารถจัดการเลขสำนักได้',
                'order' => 3,
            ],
            [
                'name' => 'จัดการข้อมูลหวย',
                'slug' => 'manage-lotto-data',
                'route_name' => 'backoffice.lotto-data.*',
                'description' => 'สามารถจัดการข้อมูลหวยย้อนหลังได้',
                'order' => 4,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
