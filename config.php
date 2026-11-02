<?php
    try {
        $conn = mysqli_connect("localhost", "root", "", "High_Heaven_Pavillion");
        mysqli_set_charset($conn, "utf8mb4");
    } catch (mysqli_sql_exception $e) {
        error_log("DB connection failed: " . $e->getMessage());
        die("Could not connect to the database. Check config.php (database name, user, password) and that MySQL is running.");
    }

?>
