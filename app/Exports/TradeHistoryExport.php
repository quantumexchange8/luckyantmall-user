<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TradeHistoryExport implements FromCollection, WithHeadings
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        $filteredData = $this->query->get();

        $result = array();
        foreach ($filteredData as $data) {
            $result[] = array(
                'meta_login' => $data->meta_login,
                'symbol' => $data->symbol,
                'time_open' => $data->time_open,
                'time_close' => $data->time_close,
                'trade_type' => $data->trade_type,
                'ticket' => $data->ticket,
                'price_open' => $data->price_open,
                'price_close' => $data->price_close,
                'volume' => $data->volume,
                'trade_profit' => $data->trade_profit,
                'trade_swap' => $data->trade_swap,
                'trade_profit_pct' => $data->trade_profit_pct,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            trans('public.account'),
            trans('public.trade'),
            trans('public.open'),
            trans('public.close'),
            trans('public.type'),
            trans('public.ticket'),
            trans('public.open') . '($)',
            trans('public.close') . '($)',
            trans('public.lot'),
            trans('public.profit') . '($)',
            trans('public.swap') . '($)',
            trans('public.change') . '(%)',
        ];
    }
}
