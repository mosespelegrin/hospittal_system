<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Permissions are additive per role; the "admin" role does not need any
     * of these assigned because AppServiceProvider grants it a blanket
     * Gate::before bypass.
     */
    public function run(): void
    {
        $permissions = [
            'view patients', 'manage patients', 'delete patients',
            'view appointments', 'manage appointments', 'delete appointments',
            'view doctors', 'manage doctors', 'delete doctors',
            'view departments', 'manage departments', 'delete departments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Front desk: full day-to-day control of patients & appointments,
        // read-only visibility into doctors/departments, no deletes.
        Role::findByName('receptionist')->syncPermissions([
            'view patients', 'manage patients',
            'view appointments', 'manage appointments',
            'view doctors',
            'view departments',
        ]);

        // Doctors get a read-only view, scoped at the query level (see
        // AppointmentResource/PatientResource) to just their own patients
        // and appointments.
        Role::findByName('doctor')->syncPermissions([
            'view patients',
            'view appointments',
        ]);
    }
}
