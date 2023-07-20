<?php
include 'database.php';

$postdata = file_get_contents("php://input");
$requestData = json_decode($postdata, true);

$dates = $requestData;

if (!empty($dates)) {
  $startDate = mysqli_real_escape_string($db, $dates['startDate']);
  $endDate = mysqli_real_escape_string($db, $dates['endDate']);

  // Query to filter the data based on the start date and end date
  $selectQuery = "SELECT bill_report.id, bill_report.bill_no, bill_report.total_amt, bill_report.date, bill_report.time, sales_report.product_id, sales_report.product_name, sales_report.product_units, sales_report.product_price, sales_report.product_quantity, sales_report.product_amount 
                  FROM bill_report 
                  JOIN sales_report ON bill_report.id = sales_report.bill_num_id
                  WHERE DATE(bill_report.date) BETWEEN '$startDate' AND '$endDate'";

  $result = $db->query($selectQuery);

  if (!$result) {
    http_response_code(500);
    die('Error executing query: ' . mysqli_error($db));
  }

  $response = [];

  while ($row = $result->fetch_assoc()) {
    $billId = $row['bill_no'];
    $date = $row['date'];
    $time = $row['time'];

    $product = [
      'prod_id' => $row['product_id'],
      'prod_name' => $row['product_name'],
      'units' => $row['product_units'],
      'price' => $row['product_price'],
      'quantity' => $row['product_quantity'],
      'amount' => $row['product_amount']
    ];

    // Check if the bill exists in the response array
    if (!isset($response[$billId])) {
      // Create a new entry for the bill in the response array
      $response[$billId] = [
        'bill_id' => $billId,
        'date' => $date,
        'time' => $time,
        'total_amount' => $row['total_amt'],
        'id' => $row['id'],
        'products' => []
      ];
    }

    // Add the product to the bill's products array
    $response[$billId]['products'][] = $product;
  }

  // Convert the response array to indexed array
  $response = array_values($response);

  // Store the response in a file
  // $responseFile = 'response.json';
  // file_put_contents($responseFile, json_encode($response));

  http_response_code(200);
  echo json_encode($response);
} else {
  http_response_code(400);
  die('Invalid request. Start date and end date are required.');
}
?>
