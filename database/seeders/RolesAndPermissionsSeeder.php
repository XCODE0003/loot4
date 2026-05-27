<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Resource subjects that authorization is scoped around.
     *
     * @var list<string>
     */
    private array $subjects = [
        'order', 'payment', 'product', 'game', 'category', 'tag', 'coupon',
        'currency', 'storage_unit', 'quote', 'ticket', 'conversion_log',
        'user', 'role', 'setting',
    ];

    /**
     * Actions available per subject.
     *
     * @var list<string>
     */
    private array $actions = ['view_any', 'view', 'create', 'update', 'delete'];

    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        // Create the full permission matrix.
        foreach ($this->subjects as $subject) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$subject}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $all = Permission::pluck('name')->all();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $support = Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);
        $finance = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);

        // Super Admin & Admin: everything.
        $superAdmin->syncPermissions($all);
        $admin->syncPermissions($all);

        // Manager: full catalog/sales/inventory, read-only users/analytics.
        $manager->syncPermissions(array_merge(
            $this->matrix(['order', 'product', 'game', 'category', 'tag', 'coupon', 'currency', 'storage_unit', 'quote'], $this->actions),
            $this->matrix(['ticket'], ['view_any', 'view', 'update']),
            $this->matrix(['user', 'conversion_log', 'payment'], ['view_any', 'view']),
        ));

        // Support: tickets full, read/update on orders & quotes, read users.
        $support->syncPermissions(array_merge(
            $this->matrix(['ticket'], $this->actions),
            $this->matrix(['order', 'quote'], ['view_any', 'view', 'update']),
            $this->matrix(['user'], ['view_any', 'view']),
        ));

        // Finance: money-related areas.
        $finance->syncPermissions(array_merge(
            $this->matrix(['order', 'payment'], ['view_any', 'view', 'update']),
            $this->matrix(['currency'], $this->actions),
            $this->matrix(['coupon', 'conversion_log'], ['view_any', 'view']),
        ));
    }

    /**
     * Build permission names for the given subjects and actions.
     *
     * @param  list<string>  $subjects
     * @param  list<string>  $actions
     * @return list<string>
     */
    private function matrix(array $subjects, array $actions): array
    {
        $names = [];

        foreach ($subjects as $subject) {
            foreach ($actions as $action) {
                $names[] = "{$action}_{$subject}";
            }
        }

        return $names;
    }
}
