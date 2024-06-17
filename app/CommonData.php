<?php

namespace App;

use App\Models\AccumulatedCredit;
use App\Models\MemberData;
use App\Models\Payslip;
use App\Models\UssdSession;
use Illuminate\Support\Facades\Log;

trait CommonData
{
    function passed_authentication($national_id, $ecNumber): bool
    {
        Log::info("User input inside f1: " . $national_id . " " . $ecNumber);
        if (!empty($national_id) && !empty($ecNumber)) {
            $memberRecord = MemberData::query()
                ->where('national_id', '=', $national_id)
                ->where('ecNumber', '=', $ecNumber)
                ->first();

            Log::info("member record is ", [$memberRecord]);

            if ($memberRecord != null) {
                $_SESSION['national_id'] = $memberRecord->national_id;
                $_SESSION['ecNumber'] = $memberRecord->ecNumber;
                $_SESSION['name'] = $memberRecord->name;
                $_SESSION['surname'] = $memberRecord->surname;
                $_SESSION['memberType'] = $memberRecord->memberType;
                $_SESSION['date_of_birth'] = $memberRecord->dob;
                $_SESSION['date_joined_fund'] = $memberRecord->doj;
                $_SESSION['doe'] = $memberRecord->doe;
                $_SESSION['memberStatus'] = $memberRecord->memberStatus;
                $_SESSION['memberCategory'] = $memberRecord->memberCategory;
                $_SESSION['life_status'] = $memberRecord->lifeStatus;
                Log::info("User logged in successfully: " . $_SESSION['name'] . " " . $_SESSION['surname'] . " " . $_SESSION['ecNumber']);

                return $this->is_authenticated();
            }
        }
        return false;
    }

    function is_authenticated(): bool
    {
        return isset($_SESSION['national_id']) && isset($_SESSION['ecNumber']) && isset($_SESSION['name']) && isset($_SESSION['memberType']) && isset($_SESSION['surname']);
    }


    function display_active_member_menu(): string
    {

        return "Active Member Menu:\n1. Check Member Information\n2. Check Accumulated Credit\n3. Check Loan Balance";
    }

    function display_pensioner_menu(): string
    {
        return "Pensioner Menu:\n1. Check Personal Information\n2. Check Current Payslip\n3. Check Life Certificate Status";
    }

    function display_frozen_member_menu(): string
    {
        return "Frozen Member Menu:\n1. Check Personal Information\n2. Check Accumulated Capital";
    }

    function displayPersonalInformation($ecNumber)
    {
        if (!empty($ecNumber)) {

            $memberRecord = MemberData::query()
                ->where('ecNumber', '=', $ecNumber)
                ->first();

            if ($memberRecord != null) {
                $details = [];
                $details['national_id'] = $memberRecord->national_id;
                $details['name'] = $memberRecord->name;
                $details['surname'] = $memberRecord->surname;
                $details['dob'] = $memberRecord->dob;
                $details['doj'] = $memberRecord->doj;
                $details['doe'] = $memberRecord->doe;
                $details['memberType'] = $memberRecord->memberType;
                $details['memberStatus'] = $memberRecord->memberStatus;
                $details['pin'] = $memberRecord->pin;
                $details['memberCategory'] = $memberRecord->memberCategory;
                $details['ecNumber'] = $memberRecord->ecNumber;
                $details['lifeStatus'] = $memberRecord->lifeStatus;
                return $details;
            }
        }
        return false;
    }

    function displayAccountBalance($ecNumber)
    {

        if (!empty($ecNumber)) {
            $credit = AccumulatedCredit::query()->where('ecNumber', '=', $ecNumber)->first();
            if ($credit != null) {
                $details = [];
                $details['ecNumber'] = $credit->ecNumber;
                $details['valuationDate'] = $credit->valuationDate;
                $details['zwlInterest'] = $credit->zwlInterest;
                $details['usdInterest'] = $credit->usdInterest;
                $details['zwlOpening'] = $credit->zwlOpening;
                $details['zwlClosing'] = $credit->zwlClosing;
                $details['usdOpening'] = $credit->usdOpening;
                $details['usdClosing'] = $credit->usdClosing;
                return $details;
            }
        }
        return false;
    }

    function displayPayslip($ecNumber)
    {
        if (!empty($ecNumber)) {
            $payslip = Payslip::query()->where('ecNumber', '=', $ecNumber)->first();
            if ($payslip) {
                $details = [];
                $details['ecNumber'] = $payslip->ecNumber;
                $details['gross'] = $payslip->gross;
                $details['deductions'] = $payslip->deductions;
                $details['net'] = $payslip->net;
                return $details;
            }
        }
        return false;
    }

    function retrieveSession($sessionId, $msisdn): UssdSession
    {
        return UssdSession::where('session_id', '=', $sessionId)
            ->where('msisdn', '=', $msisdn)
            ->first();
    }

    function clearSession($sessionId, $msisdn)
    {
        UssdSession::query()->where('session_id', '=', $sessionId)
            ->where('msisdn', '=', $msisdn)
            ->delete();
    }

    function explodePayloadText(UssdSession $ussdSession)
    {
        return explode('*', $ussdSession->payload_text);
    }

    function incrementAndUpdateStage(UssdSession $ussdSession, $payload)
    {
        $stage = $ussdSession->stage;
        $currentPayload = $ussdSession->payload_text;
        $finalPayload = $currentPayload . "*" . $payload;
        if (empty($currentPayload) and !empty($payload) and ($stage == 0 || $stage == 1)) {
            $finalPayload = null;
        }
        if (empty($currentPayload) and $stage == 1) {
            $finalPayload = $payload;
        }


        $ussdSession->stage = ++$stage;
        $ussdSession->payload_text = $finalPayload;

        $ussdSession->save();
    }
}
