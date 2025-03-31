<?php

namespace App\Enums;
enum RoleSystem: string
{
    case Admin = 'admin';
    case User = 'user';
}

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}