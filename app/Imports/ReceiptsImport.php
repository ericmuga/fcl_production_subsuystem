<?php

namespace App\Imports;

use App\Receipt;
use Maatwebsite\Excel\Concerns\ToModel;

class ReceiptsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Receipt([
            //
        ]);
    }
}
