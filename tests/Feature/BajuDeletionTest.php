<?php

namespace Tests\Feature;

use App\Models\Baju;
use App\Models\NotifikasiStok;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BajuDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_baju_also_deletes_related_transactions_and_stock_notifications(): void
    {
        $baju = Baju::factory()->create();
        $user = User::factory()->create();

        $notification = NotifikasiStok::factory()->create([
            'baju_id' => $baju->id,
        ]);

        $transaction = Transaksi::factory()->create([
            'user_id' => $user->id,
            'baju_id' => $baju->id,
        ]);

        $baju->delete();

        $this->assertDatabaseMissing('bajus', [
            'id' => $baju->id,
        ]);

        $this->assertDatabaseMissing('notifikasi_stoks', [
            'id' => $notification->id,
        ]);

        $this->assertDatabaseMissing('transaksis', [
            'id' => $transaction->id,
        ]);
    }
}
