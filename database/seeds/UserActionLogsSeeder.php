<?php

use Illuminate\Database\Seeder;

class UserActionLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Users::class, 50)->create();
    }
}
