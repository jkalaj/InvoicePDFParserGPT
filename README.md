# PDF Invoice Parser
This repository contains a PHP application that processes PDF invoices, converts them to text using the xpdf library, and then uses the ChatGPT API to parse and extract specific invoice details. The extracted details are then stored in a MySQL database. This tool simplifies the extraction of structured data from messy PDF files, making it easier to handle invoice processing.

# Features
Converts PDF files to text using xpdf <br>
Extracts invoice details using the ChatGPT API <br>
Stores extracted data in a MySQL database<br>
Supports multiple PDF file uploads<br>

# Dependencies
PHP<br>
Composer<br>
xpdf (pdftotext)<br>
Spatie PDF to Text<br>
Guzzle HTTP (any http client will do although processing time varies)<br>

# Installation
### 1. Install PHP and Composer
Make sure you have PHP and Composer installed on your system.

### 2. Install xpdf tools
Download and install the xpdf tools from xpdfreader.com.

### 3. Install PHP dependencies
Navigate to your project directory and run:<br>
composer require spatie/pdf-to-text guzzlehttp/guzzle

### 4. Setup MySQL Database
Create a MySQL database and table for storing the parsed invoice data:<br>
CREATE DATABASE pdf_parsed_data;

USE pdf_parsed_data;

CREATE TABLE invoice_data (<br>
    id INT AUTO_INCREMENT PRIMARY KEY,<br>
    InvoiceNumber VARCHAR(255),<br>
    InvoiceDate DATE,<br>
    PONumber VARCHAR(255),<br>
    TotalPrice FLOAT,<br>
    Taxes FLOAT,<br>
    Freight FLOAT<br>
);<br>

### 5. Configure the Application
Update the database credentials and paths in your PHP script as needed.

### 6. Running the Application
Start your PHP server and navigate to the upload page. Here is an example using PHP's built-in server:<br>
php -S localhost:8000<br>
Navigate to http://localhost:8000/upload.php in your web browser to upload PDF files for processing.

# Usage
### Upload PDF Files
### Navigate to the upload page and select the PDF files you want to process. Click the "Upload PDFs" button to start the upload and processing.
```
<!DOCTYPE html>
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
```
# Processing PDFs
### The uploaded PDFs are converted to text, and the text is sent to the ChatGPT API for parsing. The extracted details are then stored in the MySQL database.

# Sample Code
### The PHP script performs the following main tasks:

Converts PDF files to text using xpdf.<br>
Sends the extracted text to the ChatGPT API to extract specific invoice details.<br>
Stores the extracted details in a MySQL database.<br>
Refer to the upload.php file in this repository for the complete implementation.<br>

# License
This project is licensed under the MIT License - see the LICENSE file for details.
