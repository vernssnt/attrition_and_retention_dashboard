<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PowerBIService
{
    public function refreshDataset()
    {
        $workspaceId = "me";
        $datasetId = "8597e386-f7a1-47e1-b20f-8552f41cc23f";

        $accessToken = env('POWERBI_TOKEN');

        $url = "https://api.powerbi.com/v1.0/myorg/groups/$workspaceId/datasets/$datasetId/refreshes";

        return Http::withToken($accessToken)
            ->post($url);
    }
}