<?php

namespace App;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class CustomResponse extends ResponseBuilder
{
    protected function buildResponse(bool $success, int $api_code,
                                     $message_or_api_code, array $lang_args = null,
                                     $data = null, array $debug_data = null): array
    {
        // tell ResponseBuilder to do all the heavy lifting first
        $response = parent::buildResponse($success, $api_code, $message_or_api_code, $lang_args, $data, $debug_data);

        // then do all the tweaks you need
        $date = new DateTime();
        $response['timestamp'] = $date->getTimestamp();
        $response['timezone'] = $date->getTimezone();

        unset($response['locale']);

        // finally, return what $response holds
        return $response;
    }

}
