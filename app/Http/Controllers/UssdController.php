<?php

namespace App\Http\Controllers;

use App\Http\Enums\Protocols;
use App\Models\AccessToken;
use App\ProtocolHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UssdController extends Controller
{
    use ProtocolHelper;

    public function index(Request $request)
    {
        $app = AccessToken::where('token', $request->bearerToken())->first();

        if (is_null($app))
            $app = AccessToken::find(2);

        if (Protocols::parse($app->protocol_id) == Protocols::REST()) {
            //Call Rest parser.
        } else if (Protocols::parse($app->protocol_id) == Protocols::SOAP()) {
            //Call the XML Parser
            $formattedXMLResponse = $this->formatXMLRequest($request->getContent());

            $formattedXMLResponse['appId'] = $app->id;

            //handle the session.
            $session_id = $this->getUssdSession($formattedXMLResponse);

            $formattedXMLResponse['shortCode'] = $app->short_code;

            $formattedXMLResponse['networkName'] = $app->app_name;

            $formattedXMLResponse['countryName'] = 'Zimbabwe';

            try {
                //Map the data for to conform to the request sent to Main USSD.
                $ussdResponse = $this->restCallToUSSD($this->mapArrayForUSSD($formattedXMLResponse), $formattedXMLResponse['stage'] == 'FIRST', $session_id);

                if ($ussdResponse['responseExitCode'] != 200) {
                    throw new \Exception($ussdResponse['message']);
                }

                $message = $ussdResponse['message'];
                $stage = "MENU_PROCESSING";
                if ($ussdResponse['shouldClose']) {
                    $stage = "COMPLETE";
                }

                $formattedXMLResponse['stage'] = $stage;
                $formattedXMLResponse['message'] = $message;
                $formattedXMLResponse['code'] = 200;

                return $this->xmlResponder($formattedXMLResponse);
            } catch (\Exception $e) {

                Log::info("something bad happened here ", [$e->getMessage()]);

                $formattedXMLResponse['stage'] = 'COMPLETE';
                $formattedXMLResponse['message'] = $e->getMessage();
                $formattedXMLResponse['code'] = 500;

                return $this->xmlResponder($formattedXMLResponse);

            }
        }
    }

    private function restCallToUSSD($body, $is_start, $session_id)
    {
        $body['text'] = $session_id ? $body['text'] : "";
        $body['sessionId'] = $session_id;
        try {
            $dataProcessor = new UssdBackendController();
            return $dataProcessor->process($body);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }


    private function xmlResponder($data)
    {
        header('Content-type: text/xml; charset=utf-8');
        $final_response = '<?xml version="1.0" encoding="UTF-8"?>';
        $final_response .= '<messageResponse xmlns="http://econet.co.zw/intergration/messagingSchema">';
        $final_response .= '<transactionTime>' . $data['transactionTime'] . '</transactionTime>';
        $final_response .= '<transactionID>' . $data['transactionID'] . '</transactionID>';
        $final_response .= '<sourceNumber>' . $data['sourceNumber'] . '</sourceNumber>';
        $final_response .= '<destinationNumber>' . $data['destinationNumber'] . '</destinationNumber>';
        $final_response .= '<message>' . $data['message'] . '</message>';
        $final_response .= '<stage>' . $data['stage'] . '</stage>';
        $final_response .= '<channel>USSD</channel>';
        $final_response .= '<applicationTransactionID>APP.' . $data['appId'] . '</applicationTransactionID> ';
        $final_response .= '<transactionType>' . $data['stage'] . '</transactionType>';
        $final_response .= '</messageResponse>';

        return response($final_response, $data['code'], [
            'Content-Type' => 'application/xml'
        ]);
    }
}
