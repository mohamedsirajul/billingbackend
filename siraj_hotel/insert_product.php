<?php

include 'database.php';

$postdata = file_get_contents("php://input");
$requestData = json_decode($postdata, true);

$products = $requestData;
if (!empty($products)) {
  $insertedProducts = [];
  $insertedBill = [];
  $latestOrderID = '';

  // Get the latest order ID from the database
  $stmt = $db->query('SELECT MAX(`id`) as max_order_id FROM `bill_report`');
  $result = $stmt->fetch_assoc();
  $latestOrderID = $result['max_order_id'];

  $counter = (int)$latestOrderID + 1;
  $orderID = $counter;

  $billNumber = 'BL' . str_pad($counter, 5, '0', STR_PAD_LEFT);
  
  date_default_timezone_set('Asia/Kolkata'); // Replace 'Your_Timezone' with the desired timezone
  
  $currentDate = date('Y-m-d'); // Get current date
  $currentTime = date('H:i:s'); // Get current time

  // Insert data into bill_report table
  $bill = "INSERT INTO `bill_report`(`id`, `bill_no`, `total_amt`, `date`, `time`) VALUES 
  ('$orderID','$billNumber','0','$currentDate','$currentTime')";

  if ($db->query($bill)) {
    $insertedBill[] = [
      'id' => $orderID,
      'bill_id' => $billNumber,
      'date' => $currentDate,
      'total_amt' => '0',
      'time' => $currentTime
    ];
  } else {
    http_response_code(500);
    die('Error inserting data into the bill_report table.');
  }

  // Join the tables and insert the data
  foreach ($products as $productData) {
    $proID = mysqli_real_escape_string($db, trim($productData['id']));
    $proName = mysqli_real_escape_string($db, trim($productData['name']));
    $proUnits = mysqli_real_escape_string($db, trim($productData['units']));
    $proPrice = mysqli_real_escape_string($db, (string)$productData['price']);
    $proQuantity = mysqli_real_escape_string($db, (string)$productData['quantity']);
    $proAmount = mysqli_real_escape_string($db, (string)$productData['amount']);
    $date = mysqli_real_escape_string($db, (string)$productData['date']);
    $time = mysqli_real_escape_string($db, (string)$productData['time']);

    // Update total amount in bill_report table
    $updateBill = "UPDATE `bill_report` SET `total_amt` = `total_amt` + '$proAmount' WHERE `id` = '$orderID'";

    if ($db->query($updateBill)) {
      // Insert data into sales_report table
      $sql = "INSERT INTO `sales_report` (`bill_num_id`, `product_id`, `product_name`, `product_units`, `product_price`, `product_quantity`, `product_amount`, `date`, `time`) 
              VALUES ('$orderID', '$proID', '$proName', '$proUnits', '$proPrice', '$proQuantity', '$proAmount', '$currentDate', '$currentTime')";

      if ($db->query($sql)) {
        $insertedProducts[] = [
          'product_id' => $proID,
          'product_name' => $proName,
          'product_units' => $proUnits,
          'product_price' => $proPrice,
          'product_quantity' => $proQuantity,
          'product_amount' => $proAmount,
          'date' => $currentDate,
          'time' => $currentTime
        ];
      } else {
        http_response_code(500);
        die('Error inserting data into the sales_report table.');
      }
    } else {
      http_response_code(500);
      die('Error updating total amount in the bill_report table.');
    }
  }

  // Retrieve the inserted data using the join query
  $selectQuery = "SELECT DISTINCT bill_report.id, bill_report.bill_no, bill_report.total_amt, bill_report.date, bill_report.time, sales_report.product_id, sales_report.product_name, sales_report.product_units, sales_report.product_price, sales_report.product_quantity, sales_report.product_amount 
                  FROM bill_report 
                  JOIN sales_report ON bill_report.id = sales_report.bill_num_id";

  $result = $db->query($selectQuery);
  $insertedData = $result->fetch_all(MYSQLI_ASSOC);

  $response = [
    'bill_number' => $billNumber,
    'id' => $orderID,
    'date' => $currentDate,
    'time' => $currentTime,
    'products' => $insertedProducts,
    'data' => $insertedData
  ];

  http_response_code(201);
  echo json_encode($response);
} else {
  http_response_code(400);
  die('No products found in the request.');
}
?>
