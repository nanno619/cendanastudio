<?php

namespace App\Enums;

/**
 * Cendana Studio's staff roles, stored by name in spatie/laravel-permission's `roles` table.
 */
enum Role: string
{
    /** Bypasses every permission check through Gate::before in AppServiceProvider. */
    case SuperAdmin = 'Super Admin';
    case Director = 'Director';
    case ProjectManager = 'Project Manager';
    case Consultant = 'Consultant';
    case Finance = 'Finance';
    case Admin = 'Admin';
    case Staff = 'Staff';
    case Support = 'Support';
    case Trainee = 'Trainee';
    case Sales = 'Sales';
}
