<?php
include 'connectfile.php';
header('Content-Type: application/json');

// Function to validate the consumer key
function validateConsumerKey($consumer_key)
{
    global $con;

    try {
        $sql = "SELECT 1 FROM device_access_tokens WHERE token = :consumer_key AND blocked = 0 LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':consumer_key', $consumer_key, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
        }
    }

// Function to extract the bearer token from the Authorization header
function getBearerToken()
{

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } else {
        
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
    }

    
    error_log("Headers: " . print_r($headers, true));

    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) { 
        $authHeader = $headers['authorization'];
    } else {
        error_log("Authorization header not found");
        return null;
    }

    
    error_log("Authorization Header: " . $authHeader);

    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        error_log("Bearer Token: " . $matches[1]);
        return $matches[1];
    }

    
    error_log("No Bearer token found in Authorization header");
    return null;
}

// Function to check and enforce rate limiting
function checkRateLimit($key, $ipAddress = null)
{
    global $con;

    $limit = 50; // Requests allowed
    $timeWindow = 60; // Time window in seconds (1 minute)
    $currentTime = time();

    
    $identifier = $ipAddress ? $ipAddress : $key;

    try {
        // Check if the identifier exists in the rate_limits table
        $sql = "SELECT request_count, last_request_time 
                FROM rate_limits 
                WHERE consumer_key = :identifier";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $elapsedTime = $currentTime - $result['last_request_time'];

            if ($elapsedTime > $timeWindow) {
                // Time window has expired, reset the counter
                $sql = "UPDATE rate_limits 
                        SET request_count = 1, last_request_time = :current_time 
                        WHERE consumer_key = :identifier";
                $stmt = $con->prepare($sql);
                $stmt->bindParam(':current_time', $currentTime, PDO::PARAM_INT);
                $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
                $stmt->execute();
            } elseif ($result['request_count'] >= $limit) {
                // Rate limit exceeded
                return false;
            } else {
                // Increment the request count
                $sql = "UPDATE rate_limits 
                        SET request_count = request_count + 1 
                        WHERE consumer_key = :identifier";
                $stmt = $con->prepare($sql);
                $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
                $stmt->execute();
            }
        } else {
            // New identifier, insert into table
            $sql = "INSERT INTO rate_limits (consumer_key, request_count, last_request_time) 
                    VALUES (:identifier, 1, :current_time)";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            $stmt->bindParam(':current_time', $currentTime, PDO::PARAM_INT);
            $stmt->execute();
        }

        return true;
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false; // Assume rate limit exceeded on error
    }
}

// Main execution flow
$consumerKey = getBearerToken();

// Check if the consumer key is valid
if (!$consumerKey) {
    http_response_code(401);
    echo json_encode(["success" => false,"error" => "Unauthorized"]);
    exit;
}
// check if the rate limit is exceeded with ip address
if (!checkRateLimit(null, $_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    echo json_encode(["success" => false,"error" => "Rate limit exceeded. Try again later."]);
    exit;
}
if (!$consumerKey || !validateConsumerKey($consumerKey)) {
    http_response_code(401);
    echo json_encode(["success" => false,"error" => "Unauthorized xcvc"]);
    exit;
}



// Your API logic here
// echo json_encode(["success" => "API request successful"]);
