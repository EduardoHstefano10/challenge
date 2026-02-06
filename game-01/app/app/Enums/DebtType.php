<?php

namespace App\Enums;

enum DebtType: string
{
    case LOAN = 'Prestamo';
    case OTHER_DEBT = 'Otra deuda';
    case CREDIT_CARD = 'Tarjeta de credito';
}
