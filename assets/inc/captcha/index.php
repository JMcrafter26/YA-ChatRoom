<?php
$parent_dir = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$path = str_repeat('../', $parent_dir);
require $path . '.assets/required.php';
require_once $path . '.assets/experiments.php';

// --- IconCaptcha Start ---
$IC_preferBrowserLanguage = true;
$IC_options = [
    'messages' => [
        'wrong_icon' => 'You\'ve selected the wrong image.',
        'no_selection' => 'No image has been selected.',
        'empty_form' => 'You\'ve not submitted any form.',
        'invalid_id' => 'The captcha was not solved or ID was invalid.',
        'form_token' => 'The form token was invalid.'
    ],
    'image' => [
        'availableIcons' => 180,
        'amount' => [
            'min' => 5,
            'max' => 8
        ],
        'rotate' => true,
        'flip' => [
            'horizontally' => true,
            'vertically' => true,
        ],
        'border' => true
    ],
    'attempts' => [
        'amount' => 5,
        'timeout' => 30 // seconds.
    ],
    'token' => true
];

$IC_jsSettings = "
    general: {
        validationPath: 'https://test.jm26.net/.assets/scripts/icon-captcha/captcha-request.php',
        fontFamily: 'Poppins, sans-serif',
        credits: 'hide',
    },
    security: {
        clickDelay: 500,
        hoverDetection: true,
        enableInitialMessage: true,
        initializeDelay: 500,
        selectionResetDelay: 3000,
        loadingAnimationDelay: 1000,
        invalidateTime: 1000 * 60 * 2,
    }
";
require_once($path . '.assets/scripts/icon-captcha/captcha.inc.php');

if(IC_postRequest()) {
    if(IC_validateSubmission()) {
        // Captcha submission was valid.
        echo 'Captcha submission was valid.';
    } else {
        // Captcha submission was not valid.
        echo 'Captcha submission was not valid: ' . IC_getError();
    }
}

//  Paste this in the form of the page you want to protect:
// <?php echo IC_getHtmlCode(); ? >
// --- IconCaptcha End ---

?>

<script>
    ;
    (function() {
        var src = '//cdn.jsdelivr.net/npm/eruda';
        if (!/eruda=true/.test(window.location) && localStorage.getItem('active-eruda') != 'true') return;
        document.write('<scr' + 'ipt src="' + src + '"></scr' + 'ipt>');
        document.write('<scr' + 'ipt>eruda.init();</scr' + 'ipt>');
    })();
</script>

<form method="post">

    <?php echo IC_getHtmlCode(); ?>
    <input type="submit" value="Submit form" class="btn btn-primary" />

</form>





<?php echo $footer; ?>