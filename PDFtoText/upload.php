<?php

require 'vendor/autoload.php';

use Spatie\PdfToText\Pdf;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$servername = "localhost";
$username = "Your DB Username";
$password = "Your DB Password";
$database = "pdf_parsed_data";


//Preprocess pdf to text
function convertPdfToText($pdfPath, $txtPath) {
    //Use absolute path to pdftotext
    $pdftotextPath = 'path-to-xpdf-lib/xpdf-tools-win-4.05/xpdf-tools-win-4.05/bin64/pdftotext.exe';  //runs on the server so server should contain this library 
    
    //Command to convert PDF to text using pdftotext
    $command = "{$pdftotextPath} \"{$pdfPath}\" \"{$txtPath}\"";

    //Execute the command
    exec($command, $output, $returnVar);

    //Handle error if unsuccessful 
    if ($returnVar !== 0) {
        throw new Exception("Error converting PDF to text. Command output: " . implode("\n", $output));
    }
}

//Parse Text with ChatGPT API
function parseInvoiceDetails($extractedText, $apiKey) {
    if (!$extractedText) {
        return 'No text extracted from PDF.';
    }
    $extractedText = mb_convert_encoding($extractedText, 'UTF-8', 'auto');
    $client = new Client();
    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        'model' => 'gpt-4o',  
        'messages' => [
            ['role' => 'system', 'content' => 'You are an intelligent assistant that extracts specific details from text.'],
            ['role' => 'user', 'content' => 
                    "Extract the following details from this invoice text and return only the data points with no labels and Make sure each invoice date is formatted as MM-DD-YYYY. 
                     If an invoice number has a period next to it, include it and the numbers following. If there is no data for taxes or shipping charges, then write a 0. Make sure each number such as for Total price, Taxes, and shipping charges that each number is a float:
                     \n\nInvoice Number, Invoice Date, PO Number, Total Price Before Taxes, Taxes, Shipping Charges.\n\nInvoice Text:\n" . $extractedText],
        ],
    ];

    try {
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
            'verify' => 'C:/certs/cacert.pem'  // Path to the CA certificate bundle
        ]);

        $body = $response->getBody();
        $responseArray = json_decode($body, true);

        if (isset($responseArray['choices'][0]['message']['content'])) {
            return $responseArray['choices'][0]['message']['content'];
        } else {
            return 'Error parsing invoice details.';
        }
    } catch (RequestException $e) {
        //error handling
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = $response->getBody()->getContents();
            echo "Error: HTTP $statusCode\n$errorBody\n";
        } else {
            echo 'Error: ' . $e->getMessage();
        }
        return null;
    }
}




if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFiles'])) {
    foreach ($_FILES['pdfFiles']['tmp_name'] as $index => $tmpName) {
        if (!empty($tmpName) && is_uploaded_file($tmpName)) {
            $pdfPath = $tmpName;
            $txtPath = 'temp/output' . $index . '.txt'; // Adjust path and add a unique identifier

            try {
                convertPdfToText($pdfPath, $txtPath);
                $text = file_get_contents($txtPath); 

                $apiKey = 'YOUR_API_KEY';
                $parsedDetails = parseInvoiceDetails($text, $apiKey);
                $output = 'outputFile.txt';
                file_put_contents($output, $parsedDetails);

                // Determine whether the content is comma-separated or line-separated
                $fileContent = file_get_contents($output);
                if (strpos($fileContent, ',') !== false) {
                    $lines = explode(',', $fileContent);
                } else {
                    $lines = explode("\n", $fileContent);
                }

                $lines = array_map('trim', $lines);

                
                // Initialize variables
                    $invNum = null;
                    $invDate = null;
                    $poNum = null;
                    $totalPrice = null;
                    $taxes = null;
                    $freight = null;

                // Assign values to variables
                if (count($lines) >= 6) {
                    $invNum = $lines[0];
                    $invDate = $lines[1];
                    $poNum = $lines[2];
                    $totalPrice = $lines[3];
                    $taxes = $lines[4];
                    $freight = $lines[5];
                }

                // Insert data into the database
                $db_link = new mysqli($servername, $username, $password, $database);
                $stmt = $db_link->prepare("INSERT INTO invoice_data (InvoiceNumber, InvoiceDate, PONumber, TotalPrice, Taxes, Freight) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $invNum, $invDate, $poNum, $totalPrice, $taxes, $freight);
                echo $invNum . "\n";
                echo $invDate . "\n";
                echo $poNum . "\n";
                echo $totalPrice . "\n";
                echo $taxes . "\n";
                echo $freight . "\n";
                $stmt->execute();
                $stmt->close();

                

                // Delete the temporary output file
                if (is_file($txtPath)) {
                    unlink($txtPath);
                }
                if (is_file($output)) {
                    unlink($output);
                }

            } catch (Exception $e) {
                echo "An error occurred:\n";
                echo $e->getMessage();
            }
        }
    }
} else {
    echo "No files uploaded.";
}
?>






<!DOCTYPE html>
<!--HTML Format-->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload PDF Files</title>
</head>
<body>
    <h1>Upload PDF Files</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="pdfFiles[]" multiple accept=".pdf">
        <button type="submit">Upload PDFs</button>
    </form>
</body>
</html>
$invNum = null;
                    $invDate = null;
                    $poNum = null;
                    $totalPrice = null;
                    $taxes = null;
                    $freight = null;


<html><head></head><body>
    <p>Add a New PO</p>
    <form method="post">
        <p>Invoice #:    <input type="text" name="invNum" size="40" value="<?php echo isset($invNum) ? $invNum : ''; ?>"></p>
        <p>Invoice Date: <input type="text" name="invDate" value="<?php echo isset($invDate) ? $invDate : ''; ?>"></p>
        <p>PO #:         <input type="text" name="poNum" value="<?php echo isset($poNum) ? $poNum : ''; ?>"></p>
        <p>Total Price:  <input type="text" name="totalPrice" value="<?php echo isset($totalPrice) ? $totalPrice : ''; ?>"></p>
        <p>Taxes:        <input type="text" name="taxes" value="<?php echo isset($taxes) ? $taxes : ''; ?>"></p>
        <p>Freight:      <input type="text" name="freight" value="<?php echo isset($freight) ? $freight : ''; ?>"></p>
        <p><input type="submit" value="Add New Invoice"/></p>
    </form>
</body>





























