<?php
// Database connection parameters
include '../../../connectfile.php';
include '../update-usage-times.php';
// include '../extract-query-params.php'; 
include 'token-validation.php'; 
header('Content-Type: application/json'); // Set the response type to JSON



/**
 * Extracts query parameters from the global $_GET array.
 *
 * This function processes the query parameters passed via the URL and makes them available
 * for further use within the script. It is typically used to handle user input from GET requests.
 *
 * @param array $queryParams The array of query parameters from the $_GET superglobal.
 *
 * @return void
 */
// extractQueryParams($_GET);

/**
*Reads the raw POST data from the input stream and decodes it from JSON format.
*
* The raw POST data is read using `file_get_contents('php://input')` and then
* decoded into a PHP associative array using `json_decode`.
*
* @var string $postData The raw POST data.
* @var array|null $data The decoded JSON data as an associative array, or null if the JSON is invalid.
*/
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$consumer_key = getBearerToken();
$linkName = implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4));
$curentfilename = basename(__FILE__, '.php');
updateUsageTimes($linkName."/".$curentfilename);

if ($consumer_key == null) {
    http_response_code(400);
    echo json_encode(['error' => 'Unauthorized','success' => false]);
    exit;
}
// Validate the consumer key
if (!validateConsumerKey($consumer_key)) {
    http_response_code(401);
    echo json_encode(['status' => 'Unauthorized','success' => false]);
    exit;
}


// the logic here 
try{



} catch (Exception $e) {

}