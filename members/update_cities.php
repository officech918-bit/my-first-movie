<?php
require_once('inc/requires.php');

if (filter_has_var(INPUT_POST, 'state_id')) {
    $state_id = filter_input(INPUT_POST, 'state_id', FILTER_VALIDATE_INT);
    $city_name = filter_input(INPUT_POST, 'city_name', FILTER_SANITIZE_STRING);

    if ($state_id !== false) {
        $stmt = $pdo->prepare("SELECT CityID, CityName FROM cities WHERE StateID = :state_id");
        $stmt->execute([':state_id' => $state_id]);
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        echo '<option value="">Select City</option>';
        foreach ($cities as $row) {
            $selected = ($city_name === $row['CityName']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($row['CityName'], ENT_QUOTES, 'UTF-8') . '" data-id="' . $row['CityID'] . '" ' . $selected . '>' . htmlspecialchars($row['CityName'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }
}

?>