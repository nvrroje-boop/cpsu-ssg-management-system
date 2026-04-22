<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function portalRoutePrefix(string $default = 'admin'): string
    {
        $routeName = request()->route()?->getName() ?? '';

        if (str_starts_with($routeName, 'admin.')) {
            return 'admin';
        }

        if (str_starts_with($routeName, 'officer.')) {
            return 'officer';
        }

        if (str_starts_with($routeName, 'student.')) {
            return 'student';
        }

        return $default;
    }

    protected function portalRouteName(string $suffix, string $default = 'admin'): string
    {
        return $this->portalRoutePrefix($default).'.'.$suffix;
    }
}
