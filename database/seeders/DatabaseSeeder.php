<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('groups')->insert([
            'name' => "Example Group 1",
            'description' => "Example Group 1",
            'keycloakGroup' => "PG_Group1",
            'keycloakAdminGroup' => "PG_Group1-Admins",
            'moderated' => 1,
            'has_mailinglist' => 0,
        ]);
        DB::table('groups')->insert([
            'name' => "Example Group 2",
            'description' => "Example Group 2",
            'keycloakGroup' => "PG_Group2",
            'keycloakAdminGroup' => "PG_Group2-Admins",
            'moderated' => 0,
            'has_mailinglist' => 0,
        ]);

        DB::table('groupmembers')->insert([
            'email' => "user@example.com",
            'group_id' => 1,
            'toBeInNextCloud' => 1,
            'toBeInMailinglist' => 1,
            'waitingForJoin' => 0,
        ]);

        DB::table('groupmembers')->insert([
            'email' => "pg_group1@example.com",
            'group_id' => 1,
            'toBeInNextCloud' => 1,
            'toBeInMailinglist' => 1,
            'waitingForJoin' => 0,
        ]);

        DB::table('groupmembers')->insert([
            'email' => "pg_group2@example.com",
            'group_id' => 2,
            'toBeInNextCloud' => 0,
            'toBeInMailinglist' => 0,
            'waitingForJoin' => 0,
        ]);

    }
}
