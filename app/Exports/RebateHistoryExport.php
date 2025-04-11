<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RebateHistoryExport implements FromCollection, WithHeadings
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
                'created_at' => $data->created_at,
                'username' => $data->trader->username,
                'meta_login' => $data->meta_login,
                'volume' => $data->volume,
                'rebate' => $data->rebate,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            trans('public.date'),
            trans('public.username'),
            trans('public.account'),
            trans('public.lot'),
            trans('public.amount') . '($)',
        ];
    }
}
