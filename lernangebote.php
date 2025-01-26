<?php
require_once('../../config.php');

if (isset($USER) && isset($USER->id) && $USER->id > 0 && $USER->auth == "oauth2") {
    $url = fetchLoginUrl();
    if ($url === null) {
        $url = "https://mintcampus.org/alle-lernangebote/";
    }
    redirect($url);
} else {
    redirect("https://mintcampus.org/alle-lernangebote/");
}


function fetchLoginUrl() {
    // Define the target URL
    $url = 'https://mintcampus.org/wp-json/custom/v1/login_url';

    // Set a stream context with a timeout of 2 seconds
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 2
        )
    ));

    // Fetch content from the URL
    $response = @file_get_contents($url, false, $context);

    // If the response was successful, decode the JSON string
    if ($response !== false) {
        $decoded = json_decode($response, true);

        // Check if decoding was successful and if the result is a string
        if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
            return $decoded; // Return the decoded string (URL)
        }
    }

    // In case of timeout, unsuccessful decode, or if decoded value is not a string, return null
    return null;
}
