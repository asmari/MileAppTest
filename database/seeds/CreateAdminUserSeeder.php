<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user = User::create([
            'name' => 'Admin Ganteng',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456')
        ]);

        $role = Role::create(['name' => 'Admin']);

        $user->assignRole([$role->id]);

        $userCustomer = User::create([
            'name' => 'Customer Ganteng',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('123456')
        ]);

        $roleCustomer = Role::create(['name' => 'Customer']);

        $userCustomer->assignRole([$roleCustomer->id]);
    }
}
