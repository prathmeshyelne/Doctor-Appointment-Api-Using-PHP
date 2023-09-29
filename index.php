<?php
// Include necessary database connection code here
$servername = "your_servername";
$username = "your_username";
$password = "your_password";
$dbname = "your_dbname";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Endpoint for listing doctors
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['endpoint']) && $_GET['endpoint'] === 'doctors') {
    $sql = "SELECT * FROM doctors";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $doctors = array();
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
        echo json_encode($doctors);
    } else {
        echo json_encode([]);
    }
}

// Endpoint for doctor detail
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['endpoint']) && $_GET['endpoint'] === 'doctor' && isset($_GET['id'])) {
    $doctorId = $_GET['id'];
    $sql = "SELECT * FROM doctors WHERE id = $doctorId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $doctorDetails = $result->fetch_assoc();
        echo json_encode($doctorDetails);
    } else {
        echo json_encode(['error' => 'Doctor not found']);
    }
}

// Endpoint for booking an appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['endpoint']) && $_POST['endpoint'] === 'book-appointment') {
    // Validate and sanitize input data (patient details, date, time, doctor)
    $patientName = filter_var($_POST['patient_name'], FILTER_SANITIZE_STRING);
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $doctorId = (int)$_POST['doctor_id'];
    
    // Validate input data
    if (empty($patientName) || empty($appointmentDate) || empty($appointmentTime) || empty($doctorId)) {
        $response = array('error' => 'Invalid input data');
        echo json_encode($response);
        exit;
    }
    
    // Check doctor's availability for the specified date and time
    $sql = "SELECT * FROM appointments WHERE doctor_id = $doctorId AND appointment_date = '$appointmentDate' AND appointment_time = '$appointmentTime'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response = array('error' => 'Doctor is not available at the specified date and time');
        echo json_encode($response);
    } else {
        // Insert the appointment into the database
        $sql = "INSERT INTO appointments (patient_name, appointment_date, appointment_time, doctor_id) 
                VALUES ('$patientName', '$appointmentDate', '$appointmentTime', $doctorId)";
        
        if ($conn->query($sql) === TRUE) {
            $response = array('success' => true);
            echo json_encode($response);
        } else {
            $response = array('error' => 'Appointment booking failed');
            echo json_encode($response);
        }
    }
}

$conn->close();
?>
