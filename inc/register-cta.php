<?php
// Ensure session is started and user object is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/requires.php';

// The $user object should be instantiated in the calling script,
// but we check for its existence to be safe.
if (!isset($user) || !is_object($user)) {
    $user = new visitor();
    if (isset($_SESSION['uid'])) {
        $user = new web_user();
    }
}
?>
<section class="section-cta">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <?php if ($user->check_session()): ?>
                    <a class="button button-border button-rounded button-white button-light button-large noleftmargin nobottommargin" href="call-for-entry.php" aria-label="View the Call for Entry page">Call for Entry</a>
                <?php else: ?>
                    <a class="button button-border button-rounded button-white button-light button-large noleftmargin nobottommargin" href="members/register.php" aria-label="Register now at My First Movie">Register Now!</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<style>
    .section-cta {
        padding: 30px 0;
        background: #0089D1;
    }
    .text-center {
        text-align: center;
    }
</style>