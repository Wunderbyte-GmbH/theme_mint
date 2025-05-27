<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

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


function fetchloginurl() {
    // Define the target URL
    $url = 'https://mintcampus.org/wp-json/custom/v1/login_url';

    // Set a stream context with a timeout of 2 seconds
    $context = stream_context_create([
        'http' => [
            'timeout' => 2,
        ],
    ]);

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
