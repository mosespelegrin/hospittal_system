<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class MakeUser extends Command
{
    protected $signature = 'app:make-user {email} {--role=admin : admin, receptionist or doctor} {--name=} {--password=}';

    protected $description = 'Create a staff user, or change an existing user role';

    public function handle(): int
    {
        $role = $this->option('role');
        if (! in_array($role, ['admin', 'receptionist', 'doctor'], true)) {
            $this->error('Role must be admin, receptionist or doctor.');

            return self::FAILURE;
        }
        Role::findOrCreate($role, 'web');

        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $password = $this->option('password') ?: $this->secret('Password (min 8 chars)');
            if (strlen((string) $password) < 8) {
                $this->error('Password must be at least 8 characters.');

                return self::FAILURE;
            }
            $user = User::create([
                'name' => $this->option('name') ?: $this->ask('Name'),
                'email' => $this->argument('email'),
                'password' => $password,
            ]);
        }

        $user->syncRoles([$role]);
        $this->info("{$user->email} is now {$role}.");

        return self::SUCCESS;
    }
}
