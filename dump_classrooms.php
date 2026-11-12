<?php
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=ruang_informatika", 'root', '');
$stmt = $pdo->query("SELECT name, grade, major FROM classrooms");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
