<?php

namespace App\Http\Controllers;

use App\Http\Enums\Protocols;
use App\Models\AccessToken;
use App\ProtocolHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
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
                if ($ussdResponse->status() != 200) {
                    throw new \Exception($ussdResponse->getContent());
                }

                $ussdResponseBody = json_decode($ussdResponse->body(), true);

                $message = $ussdResponseBody['responseExitCode'] != 200 ? $ussdResponseBody['message'] : $ussdResponseBody['ussdMenu'];

                $formattedXMLResponse['stage'] = $ussdResponseBody['shouldClose'] ? 'COMPLETE' : 'MENU_PROCESSING';
                $formattedXMLResponse['message'] = $message;
                $formattedXMLResponse['code'] = 200;

                return $this->xmlResponder($formattedXMLResponse);
            } catch (\Exception $e) {
                //Prepare a Failure message
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
        $base_url = App::environment(['local', 'staging', 'test']) ? config('app.ussd_test_url') : config('app.ussd_live_url');
        $url = $is_start ? $base_url . '/session/' . $session_id . '/start' : $base_url . '/session/' . $session_id . '/response';

        $body['text'] = $session_id ? $body['text'] : "1";

        try {
            return Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(60)->post($url, $body);

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

    public function direct(Request $request)
    {
        $xml = simplexml_load_string($request->getContent());
        $jsonFormatData = json_encode($xml);
        $data = json_decode($jsonFormatData, true);
        header('Content-type: text/xml; charset=utf-8');
        $final_response = '<?xml version="1.0" encoding="UTF-8"?>';
        $final_response .= '<messageResponse xmlns="http://econet.co.zw/intergration/messagingSchema">';
        $final_response .= '<transactionTime>'.Carbon::now().'</transactionTime>';
        $final_response .= '<transactionID>' . $data['transactionID'] . '</transactionID>';
        $final_response .= '<sourceNumber>263778234258</sourceNumber>';
        $final_response .= '<destinationNumber>908</destinationNumber>';
        $final_response .= '<message>Hello, Welcome to Zesa.</message>';
        $final_response .= '<stage>MENU_PROCESSING</stage>';
        $final_response .= '<channel>USSD</channel>';
        $final_response .= '<applicationTransactionID>ZESA</applicationTransactionID> ';
        $final_response .= '<transactionType>COMPLETE</transactionType>';
        $final_response .= '</messageResponse>';
        return response($final_response, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
