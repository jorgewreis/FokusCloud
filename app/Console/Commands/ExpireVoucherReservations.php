<?php

namespace App\Console\Commands;

use App\Services\VoucherManager;
use Illuminate\Console\Command;

class ExpireVoucherReservations extends Command
{
    protected $signature = 'fokus:expire-voucher-reservations';

    protected $description = 'Expira reservas de voucher que não foram confirmadas';

    public function handle(VoucherManager $vouchers): int
    {
        $this->info(sprintf('%d reserva(s) expirada(s).', $vouchers->expireReservations()));

        return self::SUCCESS;
    }
}
