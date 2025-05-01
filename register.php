<?php
session_start();

$host = 'localhost';
$dbname   = 'sanothimicollege'; 
$username = 'root';      
$password = '';      

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Database connection failed';
    // header("Location: index.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $address  = trim($_POST['address']);
    $phone    = trim($_POST['phone']);
    $gender   = $_POST['gender'];
    $faculty  = $_POST['faculty'];
    $program  = $_POST['program'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (
        empty($name) || empty($email) || empty($address) || empty($phone) ||
        empty($gender) || empty($faculty) || empty($program) || empty($password)
    ) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'All fields are required';
        // header("Location: index.html");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Invalid email format';
        // header("Location: index.html");
        exit;
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Invalid phone number';
        // header("Location: index.html");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Passwords do not match';
        // header("Location: index.html");
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO students 
            (name, email, address, phone, gender, faculty, program, password_hash) 
            VALUES (:name, :email, :address, :phone, :gender, :faculty, :program, :password_hash)");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':address' => $address,
            ':phone' => $phone,
            ':gender' => $gender,
            ':faculty' => $faculty,
            ':program' => $program,
            ':password_hash' => $passwordHash
        ]);

        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Registration successful';
        header("Location: index.html");
        exit;

    } catch (PDOException $e) {
        // Log error
        error_log($e->getMessage(), 3, 'errors.log');

        if ($e->getCode() == 23000) { // Duplicate entry
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Email already registered';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Registration failed';
        }
        header("Location: index.html");
        exit;
    }
}
?>