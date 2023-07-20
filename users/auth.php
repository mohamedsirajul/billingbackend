<?php
include 'database.php';

// Get the request body
$request = json_decode(file_get_contents("php://input"), true);

// Validate the request
if (!empty($request) && isset($request['username']) && isset($request['password'])) {
    $username = mysqli_real_escape_string($db, $request['username']);
    $password = mysqli_real_escape_string($db, $request['password']);

    // Check if the username exists in the users table
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $db->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            $hashedPassword = $user['password'];
            if (password_verify($password, $hashedPassword)) {
                // Password is correct, user is authenticated
                $response = [
                    "authenticated" => true,
                    "mobile/email" => $user['username'],
                    "name" => $user['name'],
                ];
                echo json_encode($response);
            } else {
                // Password is incorrect
                echo json_encode(["authenticated" => false, "debug" => "Incorrect password"]);
            }
        } else {
            // Username does not exist
            echo json_encode(["authenticated" => false, "debug" => "Username does not exist"]);
        }
    } else {
        // Error in the database query
        http_response_code(500);
        echo json_encode(["message" => "Database query failed"]);
    }
} else {
    // Invalid request
    http_response_code(400);
    echo json_encode(["message" => "Invalid request"]);
}
?>
