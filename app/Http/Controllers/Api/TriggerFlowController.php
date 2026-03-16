<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class TriggerFlowController extends Controller
{

    public function runFlow()
    {

        $url = "https://defaulta36dd3dc920844b9a84e1b0808cd56.eb.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/6e048c6a4ac74ac99aea6990bfbd9d4c/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=ec4Qk_14acOnlduyM7pLQ5w5CPC6lc48IzwI7XUoN3k";

        $data = [
            "name" => "Dashboard",
            "message" => "Manual Refresh Triggered"
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return response()->json([
                "error" => curl_error($ch)
            ], 500);
        }

        curl_close($ch);

        return response()->json([
            "status" => "Flow Triggered Successfully"
        ]);
    }

}