<?php

namespace App\Http\Controllers;

use App\CommonData;
use Illuminate\Support\Facades\Log;

class UssdBackendController extends Controller
{
    use CommonData;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function process(array $request): array
    {
        $sessionId = $request['sessionId'];
        $msisdn = $request['msisdn'];
        $payload = $request['text'];
        $currentSession = $this->retrieveSession($sessionId, $msisdn);

        if ($currentSession->stage == 0) {
            $response['message'] = "Welcome to ZEIPF and ZESA Staff Pension Fund USSD Portal, enter your EC number as given by your employer: \n";
            $response['responseExitCode'] = 200;
            $response['shouldClose'] = false;
            $this->incrementAndUpdateStage($currentSession, $payload);
            return $response;
        }
        if ($currentSession->stage == 1) {
            $response['message'] = "Enter your National ID in format 632047739L50:";
            $response['responseExitCode'] = 200;
            $response['shouldClose'] = false;
            $this->incrementAndUpdateStage($currentSession, $payload);
            return $response;
        }

        if ($currentSession->stage == 2) {
            //validate id number
            if ($this->passed_authentication($payload, $currentSession->payload_text)) {
                $member_type = $_SESSION['memberType'];
                Log::info("Member Type: " . $member_type);
                // Display menu based on member type
                switch ($member_type) {
                    case 'active':
                        $response['message'] = $this->display_active_member_menu();
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = false;
                        $this->incrementAndUpdateStage($currentSession, $payload);
                        return $response;
                    case 'pensioner':
                        $response['message'] = $this->display_pensioner_menu();
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = false;
                        $this->incrementAndUpdateStage($currentSession, $payload);
                        return $response;
                    case 'frozen':
                        $response['message'] = $this->display_frozen_member_menu();
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = false;
                        $this->incrementAndUpdateStage($currentSession, $payload);
                        return $response;
                    default:
                        $response['message'] = "Invalid member type. Please contact support.";
                        $response['responseExitCode'] = 500;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                }
            } else {
                $response['message'] = "Authentication failed. Please try again.";
                $response['responseExitCode'] = 500;
                $response['shouldClose'] = true;
                $this->clearSession($sessionId, $msisdn);
                return $response;
            }
        }

        $ussd_string_exploded = $this->explodePayloadText($currentSession);

        if ($currentSession->stage == 3 and $this->passed_authentication($ussd_string_exploded[1], $ussd_string_exploded[0])) {
            $selectedOption = $payload;
            switch ($_SESSION['memberType']) {
                case 'active':
                    if ($selectedOption == '1') {
                        $personalInformation = $this->displayPersonalInformation($ussd_string_exploded[0]);
                        $response['message'] = "Member Information\n";
                        $response['message'] .= "Name: " . $personalInformation['name'] . "\n";
                        $response['message'] .= "Surname: " . $personalInformation['surname'] . "\n";
                        $response['message'] .= "National ID: " . $personalInformation['national_id'] . "\n";
                        $response['message'] .= "Date of Birth: " . $personalInformation['dob'] . "\n";
                        $response['message'] .= "Date Joined Fund: " . $personalInformation['doj'] . "\n";
                        $response['message'] .= "Date of Exit: " . ($personalInformation['doe'] != "1900-01-00" ? $personalInformation['doe'] : "Not Applicable") . "\n";
                        $response['message'] .= "Status: " . $personalInformation['memberStatus'] . "\n";
                        $response['message'] .= "Membership Category: " . $personalInformation['memberCategory'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;

                        $this->clearSession($sessionId, $msisdn);
                        return $response;

                    } elseif ($selectedOption == '2') {
                        $response['message'] = "Accumulated Credit Summary\n";
                        $accountBalance = $this->displayAccountBalance($ussd_string_exploded[0]);
                        error_log("Account Balance: " . $accountBalance['zwlOpening']);
                        error_log("Account Interest: " . $accountBalance['zwlInterest']);
                        error_log("Account Closing: " . $accountBalance['zwlClosing']);
                        error_log("Account Date: " . $accountBalance['valuationDate']);
                        $response['message'] .= "Last Valuation Date: " . $accountBalance['valuationDate'] . "\n";
                        $response['message'] .= "ZWL Opening Balance: " . $accountBalance['zwlOpening'] . " ZWL Interest: " . $accountBalance['zwlInterest'] . " ZWL Closing " . $accountBalance['zwlClosing'] . "\n";
                        $response['message'] .= "USD Opening Balance: " . $accountBalance['usdOpening'] . " USD Interest: " . $accountBalance['usdInterest'] . " USD Closing " . $accountBalance['usdClosing'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    } elseif ($selectedOption == '3') {
                        $response['message'] = "You have no loan currently\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;

                    } else {
                        $response['message'] = "Invalid option selected, please try again\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    }

                case 'pensioner':
                    if ($selectedOption == '1') {
                        $personalInformation = $this->displayPersonalInformation($ussd_string_exploded[0]);
                        $response['message'] = "Member Info\n";
                        $response['message'] .= "Name: " . $personalInformation['name'] . "\n";
                        $response['message'] .= "Surname: " . $personalInformation['surname'] . "\n";
                        $response['message'] .= "ID: " . $personalInformation['national_id'] . "\n";
                        $response['message'] .= "DOB: " . $personalInformation['dob'] . "\n";
                        $response['message'] .= "DJF: " . $personalInformation['doj'] . "\n";
                        $response['message'] .= "DOE: " . ($personalInformation['doe'] != "1900-01-00" ? $personalInformation['doe'] : "Not Applicable") . "\n";
                        $response['message'] .= "Status: " . $personalInformation['memberStatus'] . "\n";
                        $response['message'] .= "Category: " . $personalInformation['memberCategory'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    } elseif ($selectedOption == '2') {
                        $response['message'] = "Payslip Summary\n";
                        $payslipDetail = $this->displayPayslip($ussd_string_exploded[0]);
                        $response['message'] .= "Gross Pension: " . "ZWL " . $payslipDetail['gross'] . "\n";
                        $response['message'] .= "Total Deductions: " . "ZWL " . $payslipDetail['deductions'] . "\n";
                        $response['message'] .= "Net Pension: " . "ZWL " . $payslipDetail['net'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;

                    } elseif ($selectedOption == '3') {
                        switch ($_SESSION['life_status']) {
                            case 'active':
                                $response['message'] = "You are currently active, thanks for submitting your life certificate on time\n";
                                $response['responseExitCode'] = 200;
                                $response['shouldClose'] = true;
                                $this->clearSession($sessionId, $msisdn);
                                return $response;
                            case 'suspended':
                                $response['message'] = "You are currently suspended, please submit your life certificate\n";
                                $response['responseExitCode'] = 200;
                                $response['shouldClose'] = true;
                                $this->clearSession($sessionId, $msisdn);
                                return $response;
                            default:
                                $response['message'] = "An error occurred. Please try again.";
                                $response['responseExitCode'] = 200;
                                $response['shouldClose'] = true;
                                $this->clearSession($sessionId, $msisdn);
                                return $response;
                        }
                    } else {
                        $response['message'] = "Invalid option selected, please start again";
                        $response['responseExitCode'] = 500;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    }

                case 'frozen':
                    if ($selectedOption == '1') {
                        $personalInformation = $this->displayPersonalInformation($ussd_string_exploded[0]);
                        $response['message'] = "Member Info\n";
                        $response['message'] .= "Name: " . $personalInformation['name'] . "\n";
                        $response['message'] .= "Surname: " . $personalInformation['surname'] . "\n";
                        $response['message'] .= "ID: " . $personalInformation['national_id'] . "\n";
                        $response['message'] .= "DOB: " . $personalInformation['dob'] . "\n";
                        $response['message'] .= "DJF: " . $personalInformation['doj'] . "\n";
                        $response['message'] .= "DOE: " . ($personalInformation['doe'] != "1900-01-00" ? $personalInformation['doe'] : "Not Applicable") . "\n";
                        $response['message'] .= "Status: " . $personalInformation['memberStatus'] . "\n";
                        $response['message'] .= "Category: " . $personalInformation['memberCategory'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    } elseif ($selectedOption == '2') {
                        //it says check accumulated capital but it returns accumulated credit
                        $response['message'] = "Accumulated Credit\n";
                        $accountBalance = $this->displayAccountBalance($ussd_string_exploded[0]);
                        $response['message'] .= "Last Valuation Date: " . $accountBalance['valuationDate'] . "\n";
                        $response['message'] .= "ZWL Opening Balance: " . $accountBalance['zwlOpening'] . " ZWL Interest: " . $accountBalance['zwlInterest'] . " ZWL Closing " . $accountBalance['zwlClosing'] . "\n";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    } else {
                        $response['message'] = "Invalid option selected, please try again";
                        $response['responseExitCode'] = 200;
                        $response['shouldClose'] = true;
                        $this->clearSession($sessionId, $msisdn);
                        return $response;
                    }

                default:
                    $response['message'] = "Invalid member type. Please contact support.";
                    $response['responseExitCode'] = 500;
                    $response['shouldClose'] = true;
                    $this->clearSession($sessionId, $msisdn);
                    return $response;
            }

        }
        $response['message'] = "An error occurred. Please try again.";
        $response['responseExitCode'] = 500;
        $response['shouldClose'] = true;
        return $response;

    }
}
