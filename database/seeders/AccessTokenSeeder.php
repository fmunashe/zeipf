<?php

namespace Database\Seeders;

use App\Models\AccessToken;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accessTokens = [
            ['id' => 1, 'app_name' => 'Zesa Ussd', 'protocol_id' => 2, 'token' => Random::generate(), 'expiration_date' => Carbon::now()->addYears(2), 'short_code' => '543', 'status' => 1],
            ['id' => 2, 'app_name' => 'Zesa Ussd', 'protocol_id' => 1, 'token' => Random::generate(), 'expiration_date' => Carbon::now()->addYears(2), 'short_code' => '543', 'status' => 1]
        ];
        foreach ($accessTokens as $token) {
            AccessToken::query()->create($token);
        }
    }
}
