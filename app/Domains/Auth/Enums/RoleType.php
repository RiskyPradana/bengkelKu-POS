<?php

namespace App\Domains\Auth\Enums;

enum RoleType: string
{
    case AdminPusat = 'Admin Pusat';
    case ServiceAdvisor = 'Service Advisor';
    case Kasir = 'Kasir';
    case Mekanik = 'Mekanik';
}
