<?php

use Illuminate\Database\Seeder;
use MasterSoft\User;
use MasterSoft\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_employee = Role::where("name", "user")->first();
	    $role_manager  = Role::where("name", "admin")->first();
	    $employee = new User();
	    $employee->name = "Jack Barreto López";
	    $employee->email = "jack.barreto@econosystemsperu.com";
	    $employee->password = bcrypt("123456789");
	    $employee->save();
	    $employee->roles()->attach($role_employee);
	    $manager = new User();
	    $manager->name = "Administrador";
	    $manager->email = "administrador@econosystemsperu.com";
	    $manager->password = bcrypt("4Dm1n.1tcm.peru");
	    $manager->save();
	    $manager->roles()->attach($role_manager);
    }
}
