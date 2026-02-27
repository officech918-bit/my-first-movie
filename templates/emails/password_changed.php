<?php
/**
 * Password Changed Email Template.
 *
 * This template is used to generate the email content for password changed confirmations.
 *
 * @package MFM
 * @subpackage Templates
 */

declare(strict_types=1);

// Ensure all variables are set to avoid warnings/errors
$first_name = $first_name ?? 'User';
$company_name = $company_name ?? 'MyFirstMovie';
$sitename = $sitename ?? '#';
?>
<html>
<body>
    <table width="780" height="265" style="border-collapse: collapse; margin: 0 auto; font-family: Arial, sans-serif;">
        <tr>
            <td height="87" style="padding: 0;">
                <img src="<?php echo htmlspecialchars($sitename, ENT_QUOTES, 'UTF-8'); ?>/members/assets/frontend/layout/img/logos/logo.png"
                     alt="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?> Logo"
                     width="225" height="95" style="display: block; border: 0;" />
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" style="padding: 20px 0;">
                <hr style="border: 0; border-top: 1px solid #069;" />
                <p>Hello <?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>,</p>
                <p>Your password for your account with <?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?> has been successfully changed.</p>
                <p>If you did not make this change, please contact our support team immediately.</p>
                <p>Thank you for using <?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>.</p>
                <p>The Team <?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></p>
                <p>If you need any help or have any comments/concerns, please contact our <a href="<?php echo htmlspecialchars($sitename, ENT_QUOTES, 'UTF-8'); ?>/contact/" style="color: #069; text-decoration: underline;">Support Team</a>.</p>
            </td>
        </tr>
    </table>
</body>
</html>