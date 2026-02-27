<?php
class webmaster extends visitor {
    private $wm_first_name;
    private $wm_last_name;
    private $wm_email;
    private $wm_contact;
    private $wm_avatar;
    private $wm_avatar_thumb;

    function __construct() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
            $userUniqueID = $_SESSION['user_id'];
            $sessionEmail = $_SESSION['user_email'];

            $stmt = database::query(
                "SELECT first_name, last_name, email, contact, avatar, avatar_thumb FROM web_users WHERE uid = ? AND email = ? AND user_type = 'webmaster'",
                [$userUniqueID, $sessionEmail]
            );
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->wm_first_name = $data['first_name'];
                $this->wm_last_name = $data['last_name'];
                $this->wm_email = $data['email'];
                $this->wm_contact = $data['contact'];
                $this->wm_avatar = $data['avatar'];
                $this->wm_avatar_thumb = $data['avatar_thumb'];
            }
        }
    }

    function get_wm_first_name() { return $this->wm_first_name; }
    function get_wm_last_name() { return $this->wm_last_name; }
    function get_wm_email() { return $this->wm_email; }
    function get_wm_contact() { return $this->wm_contact; }
    function get_wm_avatar() { return $this->wm_avatar; }
    function get_wm_avatar_thumb() { return $this->wm_avatar_thumb; }
}
?>