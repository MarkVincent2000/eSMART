<?php

namespace App\Enums;

enum GuardianRelationship: string
{
    case MOTHER = 'mother';
    case FATHER = 'father';
    case GUARDIAN = 'guardian';
    case GRANDMOTHER = 'grandmother';
    case GRANDFATHER = 'grandfather';
    case RELATIVE = 'relative';
    case OTHER = 'other';
}


