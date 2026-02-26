<?php
class admin extends visitor {
    private $admin_first_name;
    private $admin_last_name;
    private $admin_email;
    private $admin_contact;
    private $admin_avatar;
    private $admin_avatar_thumb;

    function __construct() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
            $userUniqueID = $_SESSION['user_id'];
            $sessionEmail = $_SESSION['user_email'];

            $stmt = database::query(
                "SELECT first_name, last_name, email, contact, avatar, avatar_thumb FROM web_users WHERE uid = ? AND email = ? AND user_type = 'admin'",
                [$userUniqueID, $sessionEmail]
            );
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->admin_first_name = $data['first_name'];
                $this->admin_last_name = $data['last_name'];
                $this->admin_email = $data['email'];
                $this->admin_contact = $data['contact'];
                $this->admin_avatar = $data['avatar'];
                $this->admin_avatar_thumb = $data['avatar_thumb'];
            }
        }
    }

    function get_admin_first_name() { return $this->admin_first_name; }
    function get_admin_last_name() { return $this->admin_last_name; }
    function get_admin_email() { return $this->admin_email; }
    function get_admin_contact() { return $this->admin_contact; }
    function get_admin_avatar() { return $this->admin_avatar; }
    function get_admin_avatar_thumb() { return $this->admin_avatar_thumb; }
}
?>