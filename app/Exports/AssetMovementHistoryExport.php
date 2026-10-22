<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetMovementHistoryExport implements FromQuery, WithHeadings, WithMapping
{
    private $fromDate;
    private $toDateExclusive;

    public function __construct(string $fromDate, string $toDateExclusive)
    {
        $this->fromDate = $fromDate;
        $this->toDateExclusive = $toDateExclusive;
    }

    public function query()
    {
        return DB::table('asset_movements as a')
            ->join('users', 'a.user_id', '=', 'users.id')
            ->where('a.status', 1)
            ->where('a.created_at', '>=', $this->fromDate)
            ->where('a.created_at', '<', $this->toDateExclusive)
            ->select(
                'a.fa',
                'a.description',
                'a.narration',
                'a.from_dept',
                'a.from_user',
                'a.to_user',
                'a.to_dept',
                'users.username',
                'a.authenticated_username',
                'a.status',
                'a.created_at'
            )
            ->orderByDesc('a.id');
    }

    public function map($movement): array
    {
        return [
            $movement->fa,
            $movement->description,
            $movement->narration,
            $movement->from_dept,
            $movement->from_user,
            $movement->to_user,
            $movement->to_dept,
            $movement->username,
            $movement->authenticated_username,
            $movement->status == 2 ? 'cancelled' : 'unedited',
            Carbon::parse($movement->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'FA',
            'Description',
            'Narration',
            'From Dept',
            'From Employee',
            'Assigned Employee',
            'Employee Dept',
            'Created By',
            'Authenticated Username',
            'Status',
            'Date',
        ];
    }
}
