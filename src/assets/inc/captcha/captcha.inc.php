<?php

$IC_langVersion = '1.1';



// Start a session, if not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the IconCaptcha classes.
require_once('./assets/inc/captcha/captcha-session.class.php');
require_once('./assets/inc/captcha/captcha.class.php');


use IconCaptcha\IconCaptcha;

if(!isset($IC_preferBrowserLanguage)) {
    $IC_preferBrowserLanguage = false;
} else if (isset($_GET['IC_Lang'])) {
    $IC_preferBrowserLanguage = false;
    $IC_messages = IC_getLanguageFile($_GET['IC_Lang']);
} else {
    $IC_preferBrowserLanguage = true;
    $IC_messages = IC_getLanguageFile(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
}



if(!isset($IC_options) || $IC_options == '') {
    // if the options are not set, set them to the default values.
    if($IC_preferBrowserLanguage) {
        $IC_messages = IC_getLanguageFile(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    } else {
        $IC_messages = IC_getLanguageFile('en');
    }
IconCaptcha::options([
    // 'iconPath' => __DIR__ . '/' . 'assets/icons/', // required
    'iconPath' => './assets/icons/icons/', // required

    'messages' => [
        'wrong_icon' => $IC_messages['wrong_icon'],
        'no_selection' => $IC_messages['no_selection'],
        'empty_form' => $IC_messages['empty_form'],
        'invalid_id' => $IC_messages['invalid_id'],
        'form_token' => $IC_messages['form_token']
    ],
    'image' => [
        'availableIcons' => 180, // Number of unique icons available. By default, IconCaptcha ships with 180 icons.
        'amount' => [
            'min' => 5, // The lowest possible is 5 icons per challenge.
            'max' => 8 // The highest possible is 8 icons per challenge.
        ],
        'rotate' => true,
        'flip' => [
            'horizontally' => true,
            'vertically' => true,
        ],
        'border' => true
    ],
    'attempts' => [
        'amount' => 3,
        'timeout' => 60 // seconds.
    ],
    'token' => true
]);
} else {
    // if the options are set, set them to the values specified in the options array.
    $IC_options['iconPath'] = __DIR__ . '/' . 'assets/icons/';
    if(!isset($IC_messages)) {
        $IC_messages = IC_getLanguageFile(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    }
    // if preferred language is set, update the messages array.
    if($IC_preferBrowserLanguage) {
        $IC_options['messages'] = [
            'wrong_icon' => $IC_messages['wrong_icon'],
            'no_selection' => $IC_messages['no_selection'],
            'empty_form' => $IC_messages['empty_form'],
            'invalid_id' => $IC_messages['invalid_id'],
            'form_token' => $IC_messages['form_token']
        ];
    }
    IconCaptcha::options($IC_options);
}

$IC_token = IconCaptcha::token();

if(isset($IC_jsSettings)) {
    $IC_jsSettings = "<script>
    var IC_settings = { 
        " . $IC_jsSettings . "
    }; 
    </script>";
} else {
    $IC_jsSettings = "<script>
    var IC_settings = {
        general: {
            // validationPath: './src/captcha-request.php', // required, change path according to your installation.
            validationPath: './assets/inc/captcha/captcha-request.php',
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
            invalidateTime: 1000 * 60 * 2, // 2 minutes, in milliseconds
        }
    };
    </script>";
}

function IC_getHtmlCode() {
    // global $captchaMessage;
    // global $IC_showMessage;
    // if(!isset($IC_showMessage)) {
    //     $IC_showMessage = true;
    // }
    global $IC_jsSettings;
    global $IC_token;
    global $IC_langVersion;


$IC_captchaHtmlCode = '
<div id="iconCaptchaDiv">
<input type="hidden" name="_iconcaptcha-token" value="' . $IC_token . '" />
<div class="iconcaptcha-holder" data-theme="dark" data-langVersion="' . $IC_langVersion . '"></div>
<link href="https://api.jm26.net/icon-captcha/v1/assets/css/icon-captcha.min.css" rel="stylesheet" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
' . $IC_jsSettings . '
<script src="https://api.jm26.net/icon-captcha/v1/assets/js/icon-captcha.min.js" type="text/javascript"></script>
<script src="https://api.jm26.net/icon-captcha/v1/assets/js/icon-captcha-plugin.min.js" type="text/javascript"></script>
</div>';
return $IC_captchaHtmlCode;
}

function IC_getCaptchaElement() {
    global $IC_token;
    global $IC_langVersion;
    $IC_captchaElement = '
    <input type="hidden" name="_iconcaptcha-token" value="' . $IC_token . '" />
<div class="iconcaptcha-holder" data-theme="dark" data-langVersion="' . $IC_langVersion . '"></div>
';
    return $IC_captchaElement;
}

function IC_getCaptchaDependencies() {
    global $IC_jsSettings;
    $IC_captchaDependencies = '
    <link href="https://api.jm26.net/icon-captcha/v1/assets/css/icon-captcha.min.css" rel="stylesheet" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
' . $IC_jsSettings . '
<script src="https://api.jm26.net/icon-captcha/v1/assets/js/icon-captcha.min.js" type="text/javascript"></script>
<script src="https://api.jm26.net/icon-captcha/v1/assets/js/icon-captcha-plugin.min.js" type="text/javascript"></script>
';
    return $IC_captchaDependencies;
}
    

function IC_getLanguageFile ($lang) {
    global $IC_path;
    $translationFile = file_get_contents('./assets/inc/captcha/assets/translations/' . $lang . '.json');
    if ($translationFile === false) {
        $translationFile = [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha was not solved or ID was invalid.',
            'form_token' => 'The form token was invalid.'
        ];
    } else {
        $translationFile = json_decode($translationFile, true);
    }
    return $translationFile;

  
}

function IC_validateSubmission() {
if(!empty($_POST)) {
    return IconCaptcha::validateSubmission($_POST);
} else {
    return null;
}
}

function IC_validateSubmissionGET() {
    if(!empty($_GET)) {
        return IconCaptcha::validateSubmission($_GET);
    } else {
        return null;
    }
    }

function IC_postRequest() {
    if(isset($_POST) && !empty($_POST)) {
        return true;
    } else {
        return false;
    }
}

function IC_getError() {
    global $IC_preferBrowserLanguage;
    if(isset($IC_preferBrowserLanguage)) {
        // get preferred language from browser
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        // get the language file
    } else {
        $lang = 'en';
    }
    return IconCaptcha::getErrorMessage();
}

// echo '<script>console.log("captcha.inc.php loaded");</script>';
