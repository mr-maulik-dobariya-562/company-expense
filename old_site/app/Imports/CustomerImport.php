<?php

namespace App\Imports;

use App\Models\City;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\PartyGroup;
use App\Models\State;
use App\Models\Transport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CustomerImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if($row[3] == 'DEALER'){
			$partyType = 1;
		}else if($row[3] == 'RETAIL BULK'){
			$partyType = 3;
		}else if($row[3] == 'RETAIL'){
			$partyType = 2;
		}else{
			$partyType = NULL;
		}
        if($row[27] == 'H'){
            $billType = '75';
        }else if($row[27] == 'C'){
            $billType = '50';
        }else{
            $billType = '100';
        }
        // dd($row[9] != NULL);
		return new Customer([
			'party_type_id' => $partyType,
			'name' => $row[6],
			'address' => $row[7],
            'area' => $row[8] != NULL ? $row[8] : NULL,
			'state_id' => $row[10] != NULL ? $this->findOrCreate(State::class, "name", $row[10]) : NULL,
			'city_id' => $row[9] != NULL ? $this->city(City::class, "name", $row[9],$row[10]) : NULL,
			'country_id' => 1,
			'pincode' => $row[11],
			'email' => $row[12],
			'mobile' => $row[18],
            'password' => $row[13],
			'status' => $row[4] != 'TRUE' ? 'INACTIVE' : 'ACTIVE',
			'other_transport_id' => $row[19] != NULL ? findOrCreate(Transport::class, "name", $row[19]) : NULL,
			'other_courier_id' => $row[26] != NULL ? findOrCreate(Courier::class, "name", $row[26]) : NULL,
			'party_group_id' => $row[12] != NULL ? findOrCreate(PartyGroup::class, "name", $row[14]) : NULL,
            'contact_person' => $row[16],
            'other_sample' => $row[5] ?? 'NO',
			'discount' => $row[25],
            'bill_type' => $billType,
            'price' => $row[20] == 'old'? 'Active' : 'Not Active',
            'reference' => $row[15],
            'gst' => $row[22],
            'fax' => $row[17],
            'vat' => $row[21],
		]);
    }

    public function startRow(): int
    {
        return 2; // Skip the first row
    }

    public function findOrCreate($modelClass, $field, $value)
    {
        if (!is_numeric($value)) {
            $model = $modelClass::where($field, $value)->first();
            if (!$model && $value != NULL) {
                return $modelClass::insertGetId([
                    $field       => $value,
                    'country_id' => 1,
                    "created_by" => auth()->id()
                ]);
            } else {
                return $model->id ?? NULL;
            }
        }
        return $value;
    }

    public function city($modelClass, $field, $value,$value2 = null)
    {
        if (!is_numeric($value)) {
            $model = $modelClass::where($field, $value)->first();
            $state = State::where('name', $value2)->first()->id ?? $this->findOrCreate(State::class, "name", $value2);
            if (!$model) {
                return $modelClass::insertGetId([
                    $field       => $value,
                    'country_id' => 1,
                    'state_id'   => $state,
                    "created_by" => auth()->id()
                ]);
            } else {
                return $model->id;
            }
        }
        return $value;
    }
}
