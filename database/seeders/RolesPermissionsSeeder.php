<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Events
            'events.view_any', 'events.view', 'events.create', 'events.edit.own',
            'events.edit.any', 'events.delete.own', 'events.delete.any',
            'events.publish', 'events.feature', 'events.view_analytics',
            // Ticket Types
            'ticket_types.create', 'ticket_types.edit', 'ticket_types.delete',
            // Orders
            'orders.view.own', 'orders.view.any', 'orders.refund.own', 'orders.refund.any',
            'orders.create_complimentary',
            // Attendees
            'attendees.view.own', 'attendees.view.any', 'attendees.export',
            // Check-in
            'checkin.scan', 'checkin.override', 'checkin.manage_sessions',
            // Promo Codes
            'promo_codes.create', 'promo_codes.edit', 'promo_codes.delete',
            // Payouts
            'payouts.view.own', 'payouts.view.any', 'payouts.manage',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.suspend', 'users.impersonate',
            // Platform
            'settings.view', 'settings.manage',
            'reports.view', 'reports.export',
            'activity_logs.view',
            // Venues
            'venues.create', 'venues.edit', 'venues.verify',
            // Reviews
            'reviews.moderate',
            // Waitlist
            'waitlists.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin — platform management, no destructive actions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'events.view_any', 'events.view', 'events.edit.any', 'events.delete.any',
            'events.publish', 'events.feature', 'events.view_analytics',
            'ticket_types.create', 'ticket_types.edit', 'ticket_types.delete',
            'orders.view.any', 'orders.refund.any', 'orders.create_complimentary',
            'attendees.view.any', 'attendees.export',
            'checkin.scan', 'checkin.override', 'checkin.manage_sessions',
            'promo_codes.create', 'promo_codes.edit', 'promo_codes.delete',
            'payouts.view.any', 'payouts.manage',
            'users.view', 'users.create', 'users.edit', 'users.suspend',
            'settings.view',
            'reports.view', 'reports.export',
            'venues.create', 'venues.edit', 'venues.verify',
            'reviews.moderate',
            'waitlists.manage',
        ]);

        // Organizer — their own events only
        $organizer = Role::firstOrCreate(['name' => 'organizer']);
        $organizer->syncPermissions([
            'events.view', 'events.create', 'events.edit.own', 'events.delete.own',
            'events.publish', 'events.view_analytics',
            'ticket_types.create', 'ticket_types.edit', 'ticket_types.delete',
            'orders.view.own', 'orders.refund.own', 'orders.create_complimentary',
            'attendees.view.own', 'attendees.export',
            'checkin.scan', 'checkin.manage_sessions',
            'promo_codes.create', 'promo_codes.edit', 'promo_codes.delete',
            'payouts.view.own',
            'venues.create',
            'waitlists.manage',
        ]);

        // Attendee — basic access
        $attendee = Role::firstOrCreate(['name' => 'attendee']);
        $attendee->syncPermissions([
            'events.view', 'orders.view.own', 'attendees.view.own',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
