<?php
require_once('inc/requires.php');

if (filter_has_var(INPUT_POST, 'country_id')) {
    $country_id = filter_input(INPUT_POST, 'country_id', FILTER_VALIDATE_INT);
    $state_name = filter_input(INPUT_POST, 'state_name', FILTER_SANITIZE_STRING);

    if ($country_id !== false) {
        $stmt = $database->prepare("SELECT StateID, StateName FROM states WHERE CountryID = ?");
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<option value="">Select State</option>';
        while ($row = $result->fetch_assoc()) {
            $selected = ($state_name === $row['StateName']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($row['StateName'], ENT_QUOTES, 'UTF-8') . '" data-id="' . $row['StateID'] . '" ' . $selected . '>' . htmlspecialchars($row['StateName'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $stmt->close();
    }
}

?>