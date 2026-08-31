<?php

namespace App\Domains\WorkOrder\Enums;

use InvalidArgumentException;

enum WorkOrderStatus: string
{
    case Pending = 'Pending';
    case InProgress = 'In Progress';
    case Completed = 'Completed';
    case Paid = 'Paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Paid => 'Paid',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Pending, self::InProgress], true),
            self::InProgress => in_array($target, [self::InProgress, self::Completed], true),
            self::Completed => in_array($target, [self::Completed, self::Paid], true),
            self::Paid => $target === self::Paid,
        };
    }

    public static function make(string|self $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        try {
            return self::from($status);
        } catch (\ValueError $exception) {
            throw new InvalidArgumentException("Invalid work order status: {$status}", previous: $exception);
        }
    }
}
