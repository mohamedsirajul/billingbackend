<?php
include 'database.php';

// Get the request body
$request = json_decode(file_get_contents("php://input"), true);

// Validate the request
if (!empty($request) && isset($request['name']) && isset($request['username']) && isset($request['password'])) {
    $name = mysqli_real_escape_string($db, $request['name']);
    $username = mysqli_real_escape_string($db, $request['username']);
    $password = mysqli_real_escape_string($db, $request['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $checkUsernameQuery = "SELECT * FROM `users` WHERE `username` = '$username'";
    $checkUsernameResult = $db->query($checkUsernameQuery);

    if ($checkUsernameResult->num_rows > 0) {
        // Username already exists
        $response = ["message" => "Username already exists"];
        header('Content-Type: application/json');
        http_response_code(409);
        echo json_encode($response);
    } else {
        // Insert the data into the database
        $insertUserQuery = "INSERT INTO `users`(`name`, `username`, `password`) VALUES ('$name','$username','$hashedPassword')";

        if ($db->query($insertUserQuery)) {
            $user = [
                'id' => mysqli_insert_id($db),
                'username' => $username,
            ];
            $response = [
                "message" => "User registered successfully",
                "user" => $user
            ];
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode($response);
        } else {
            $response = ["message" => "Failed to register user"];
            header('Content-Type: application/json');
            http_response_code(422);
            echo json_encode($response);
        }
    }
} else {
    $response = ["message" => "Invalid request"];
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode($response);
}
?>
