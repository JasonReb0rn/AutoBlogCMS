<?php

session_start();
require_once 'dbh.inc.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

// Debug: Check if we're receiving the file
if (empty($_FILES)) {
    echo json_encode(['success' => false, 'error' => 'No files received', 'debug' => $_POST]);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image field found', 'debug' => array_keys($_FILES)]);
    exit;
}

try {
    // Debug: Check AWS credentials
    $awsKey = $_ENV['AWS_SES_KEY'];
    $awsSecret = $_ENV['AWS_SES_SECRET'];
    
    if (empty($awsKey) || empty($awsSecret)) {
        echo json_encode(['success' => false, 'error' => 'AWS credentials not found']);
        exit;
    }

    // Configure AWS S3 client
    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-2', // Change to your region
        'credentials' => [
            'key'    => $awsKey,
            'secret' => $awsSecret,
        ]
    ]);

    $file = $_FILES['image'];
    
    // Debug: Check file details
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false, 
            'error' => 'File upload error',
            'debug' => [
                'error_code' => $file['error'],
                'file_info' => $file
            ]
        ]);
        exit;
    }

    // Validate file type
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid file type',
            'debug' => ['provided_type' => $file['type']]
        ]);
        exit;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '-' . time() . '.' . $extension;
    
    // Your S3 bucket name
    $bucket = 'autoblogcms'; // Replace with your bucket name
    
    // Debug: Verify file exists and is readable
    if (!is_readable($file['tmp_name'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Cannot read uploaded file',
            'debug' => ['tmp_name' => $file['tmp_name']]
        ]);
        exit;
    }

    // Try to upload to S3
    try {
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => 'blog-uploads/' . $filename,
            'SourceFile' => $file['tmp_name'],
            'ContentType' => $file['type']
        ]);

        echo json_encode([
            'success' => true,
            'url' => $result['ObjectURL']
        ]);

    } catch (AwsException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'S3 Upload Failed',
            'debug' => [
                'message' => $e->getMessage(),
                'aws_error' => $e->getAwsErrorMessage(),
                'request_id' => $e->getAwsRequestId(),
                'error_type' => $e->getAwsErrorType()
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'debug' => [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}