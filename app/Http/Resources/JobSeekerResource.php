<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobSeekerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'ak1_number' => $this->ak1_number,
            'nik' => $this->nik,
            'name' => $this->name,

            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,

            'gender' => $this->gender,
            'marital_status' => $this->marital_status,

            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,

            'last_education' => $this->last_education,
            'institution' => $this->institution,

            'skills' => $this->skills,
            'languages' => $this->languages,

            'desired_position' => $this->desired_position,
            'desired_location' => $this->desired_location,
            'desired_salary' => $this->desired_salary,

            'worked_last_6_months' => $this->worked_last_6_months,

            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'verified_at' => $this->verified_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}