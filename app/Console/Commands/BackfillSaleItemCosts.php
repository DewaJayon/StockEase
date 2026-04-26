<?php

namespace App\Console\Commands;

use App\Actions\Sale\RecalculateSaleTotal;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-sale-item-costs')]
#[Description('Backfill cost_price for existing sale items using current product purchase_price')]
class BackfillSaleItemCosts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill of sale item costs...');

        $items = SaleItem::with('product')->where('cost_price', 0)->orWhereNull('cost_price')->get();

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $item) {
            if ($item->product) {
                $item->update([
                    'cost_price' => $item->product->purchase_price,
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Recalculating sale total costs...');

        $saleIds = $items->pluck('sale_id')->unique();
        $sales = Sale::whereIn('id', $saleIds)->get();

        $bar = $this->output->createProgressBar($sales->count());
        $bar->start();

        foreach ($sales as $sale) {
            resolve(RecalculateSaleTotal::class)->execute($sale);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Backfill completed successfully!');
    }
}
