<?php

namespace Database\Seeders;

use App\Models\AccumulatedCredit;
use App\Models\MemberData;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 12; $i <= 15; $i++) {
            MemberData::query()->create([
                'national_id' => 'test' . $i,
                'name' => ucfirst(Str::random(5)),
                'surname' => ucfirst(Str::random(5)),
                'dob' => Carbon::now()->subYears(30)->format("Y-m-d"),
                'doj' => Carbon::now()->subYears(5)->format("Y-m-d"),
                'doe' => Carbon::now()->subYears(2)->format("Y-m-d"),
                'memberType' => 'active',
                'memberStatus' => 'active',
                'pin' => '5730',
                'memberCategory' => 'contributory',
                'ecNumber' => 'CONT' . $i,
                'lifeStatus' => 'active'
            ],
            );


            AccumulatedCredit::query()->create([
                'ecNumber' => 'CONT' . $i,
                'valuationDate' => Carbon::now()->subMonths(10),
                'zwlInterest' => 500,
                'usdInterest' => 5,
                'zwlOpening' => 300,
                'zwlClosing' => 800,
                'usdOpening' => 3,
                'usdClosing' => 8
            ]);

            $gross = rand(100, 2000);
            $deductions = rand(10, 500);
            $net = $gross - $deductions;

            Payslip::query()->create([
                'ecNumber' => 'CONT' . $i,
                'gross' => $gross,
                'deductions' => $deductions,
                'net' => $net,
            ]);
        }
    }
}
