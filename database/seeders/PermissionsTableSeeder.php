<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'meal_create',
            ],
            [
                'id'    => 18,
                'title' => 'meal_edit',
            ],
            [
                'id'    => 19,
                'title' => 'meal_show',
            ],
            [
                'id'    => 20,
                'title' => 'meal_delete',
            ],
            [
                'id'    => 21,
                'title' => 'meal_access',
            ],
            [
                'id'    => 22,
                'title' => 'subcrption_plan_create',
            ],
            [
                'id'    => 23,
                'title' => 'subcrption_plan_edit',
            ],
            [
                'id'    => 24,
                'title' => 'subcrption_plan_show',
            ],
            [
                'id'    => 25,
                'title' => 'subcrption_plan_delete',
            ],
            [
                'id'    => 26,
                'title' => 'subcrption_plan_access',
            ],
            [
                'id'    => 27,
                'title' => 'duration_create',
            ],
            [
                'id'    => 28,
                'title' => 'duration_edit',
            ],
            [
                'id'    => 29,
                'title' => 'duration_show',
            ],
            [
                'id'    => 30,
                'title' => 'duration_delete',
            ],
            [
                'id'    => 31,
                'title' => 'duration_access',
            ],
            [
                'id'    => 32,
                'title' => 'user_subcrption_create',
            ],
            [
                'id'    => 33,
                'title' => 'user_subcrption_edit',
            ],
            [
                'id'    => 34,
                'title' => 'user_subcrption_show',
            ],
            [
                'id'    => 35,
                'title' => 'user_subcrption_delete',
            ],
            [
                'id'    => 36,
                'title' => 'user_subcrption_access',
            ],
            [
                'id'    => 37,
                'title' => 'subscription_plan_day_create',
            ],
            [
                'id'    => 38,
                'title' => 'subscription_plan_day_edit',
            ],
            [
                'id'    => 39,
                'title' => 'subscription_plan_day_show',
            ],
            [
                'id'    => 40,
                'title' => 'subscription_plan_day_delete',
            ],
            [
                'id'    => 41,
                'title' => 'subscription_plan_day_access',
            ],
            [
                'id'    => 42,
                'title' => 'subscription_meal_create',
            ],
            [
                'id'    => 43,
                'title' => 'subscription_meal_edit',
            ],
            [
                'id'    => 44,
                'title' => 'subscription_meal_show',
            ],
            [
                'id'    => 45,
                'title' => 'subscription_meal_delete',
            ],
            [
                'id'    => 46,
                'title' => 'subscription_meal_access',
            ],
            [
                'id'    => 47,
                'title' => 'profile_password_edit',
            ],
        ];

        Permission::insert($permissions);
    }
}
