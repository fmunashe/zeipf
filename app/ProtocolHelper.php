<?php

namespace App;

use App\Models\UssdSession;
use Carbon\Carbon;

trait ProtocolHelper
{
    function formatXMLRequest($xmltext): array
    {
        $xmlObject = simplexml_load_string($xmltext, 'SimpleXMLElement', LIBXML_NOCDATA);
        $namespaces = $xmlObject->getNamespaces(true);
        $tns = $xmlObject->children($namespaces['tns']);

        $result = [
            'transactionTime' => (string)$tns->transactionTime,
            'transactionID' => (string)$tns->transactionID,
            'sourceNumber' => (string)$tns->sourceNumber,
            'destinationNumber' => (string)$tns->destinationNumber,
            'message' => (string)$tns->message,
            'stage' => (string)$tns->stage,
            'channel' => (string)$tns->channel,
        ];


        return [
            'transactionTime' => $result['transactionTime'],
            'transactionID' => $result['transactionID'],
            'sourceNumber' => $result['sourceNumber'],
            'destinationNumber' => $result['destinationNumber'],
            'text' => $result['message'],
            'stage' => $result['stage'],
            'channel' => $result['channel']
        ];
    }

    function mapArrayForUSSD($dataArray): array
    {
        return [
            'shortCode' => $dataArray['shortCode'],
            'networkName' => $dataArray['networkName'],
            'countryName' => $dataArray['countryName'],
            'msisdn' => $dataArray['sourceNumber'],
            'text' => $dataArray['text']
        ];
    }

    function getUssdSession($data)
    {
        $session = UssdSession::query()
            ->where('session_id', $data['transactionID'])
            ->where('msisdn', $data['sourceNumber'])
            ->first();

        if ($session !== NULL) {
            $session->updated_at = Carbon::now();
            $session->save();
            return $session->session_id;
        } else {
            UssdSession::create([
                'session_id' => $data['transactionID'],
                'msisdn' => $data['sourceNumber'],
                'app_id' => $data['appId'],
                'application_unique_id' => uniqid($data['transactionID']),
                'stage' => 0,
                'payload_text' => null,
            ]);


            return UssdSession::query()
                ->where('session_id', $data['transactionID'])
                ->orderby('created_at', 'DESC')
                ->first()->session_id;
        }

    }

}
