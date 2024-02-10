<?php
    // Include the IconCaptcha classes.
    require_once '../../assets/inc/vendor/autoload.php';

    // Start a session.
    // * Required when using any 'session' driver in the configuration.
    // * Required when using the IconCaptcha Token, referring to the use of 'IconCaptchaToken' in the form below.
    // For more information, please refer to the documentation.
    session_start();
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>IconCaptcha v4.0.3 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=11" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="../assets/favicon.ico" rel="shortcut icon" type="image/x-icon" />

        <!-- JUST FOR THE DEMO PAGE -->
        <link href="../assets/demo.css" rel="stylesheet" type="text/css">
        <script src="../assets/demo.js" type="text/javascript"></script>
        <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700" rel="stylesheet">

        <!-- Include IconCaptcha stylesheet - REQUIRED -->
        <link href="../../assets/inc/iconcaptcha/client/css/iconcaptcha.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">

            <div class="section">

                <!-- Captcha message placeholder -->
                <p class="message"></p>

                <!-- The IconCaptcha holder should ALWAYS be placed WITHIN the <form> element -->
                <form action="ajax-submit.php" method="post">

                    <!-- Additional security token to prevent CSRF. -->
                    <!-- Optional, but highly recommended - disable via IconCaptcha options. -->
                    <!-- Note: using the default IconCaptcha Token class? Make sure to start a PHP session. -->
                    <?= \IconCaptcha\Token\IconCaptchaToken::render() ?>

                    <!-- The IconCaptcha will be rendered in this element - REQUIRED -->
                    <div class="iconcaptcha-widget" data-theme="light"></div>

                    <!-- Submit button to test your IconCaptcha input -->
                    <input type="submit" value="Submit demo captcha" class="btn btn-invert">
                </form>

                <!-- Theme selector - JUST FOR THE DEMO PAGE -->
                <div class="themes">
                    <div class="theme theme--light"><span data-theme="light"></span><span>Light Theme</span></div>
                    <div class="theme theme--dark"><span data-theme="dark"></span><span>Dark Theme</span></div>
                </div>
                <small class="smaller">- The theme selector only works when no challenge has been rendered yet -</small>
            </div>
        </div>


        <!-- Include jQuery Library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

        <!-- Include IconCaptcha script - REQUIRED -->
        <script src="../../assets/inc/iconcaptcha/client/js/iconcaptcha.min.js" type="text/javascript"></script>

        <!-- Initialize the IconCaptcha - REQUIRED -->
        <script type="text/javascript">
            $(document).ready(function () {
                $('.iconcaptcha-widget').iconCaptcha({
                    general: {
                        endpoint: '../../assets/inc/captcha-request.php',
                        fontFamily: 'inherit',
                    },
                    security: {
                        interactionDelay: 1000,
                        hoverProtection: true,
                        displayInitialMessage: true,
                        initializationDelay: 500,
                        incorrectSelectionResetDelay: 3000,
                        loadingAnimationDuration: 1000,
                    },
                    locale: {
                        initialization: {
                            verify: 'Verify that you are human.',
                            loading: 'Loading challenge...',
                        },
                        header: 'Select the image displayed the <u>least</u> amount of times',
                        correct: 'Verification complete.',
                        incorrect: {
                            title: 'Uh oh.',
                            subtitle: "You've selected the wrong image."
                        },
                        timeout: {
                            title: 'Please wait.',
                            subtitle: 'You made too many incorrect selections.'
                        }
                    }
                })
                // .bind('init', function(e) { // You can bind to custom events, in case you want to execute custom code.
                //     console.log('Event: Captcha initialized', e.detail.captchaId);
                // }).bind('selected', function(e) {
                //     console.log('Event: Icon selected', e.detail.captchaId);
                // }).bind('refreshed', function(e) {
                //     console.log('Event: Captcha refreshed', e.detail.captchaId);
                // }).bind('invalidated', function(e) {
                //     console.log('Event: Invalidated', e.detail.captchaId);
                // }).bind('reset', function(e) {
                //     console.log('Event: Reset', e.detail.captchaId);
                // }).bind('success', function(e) {
                //     console.log('Event: Correct input', e.detail.captchaId);
                // }).bind('error', function(e) {
                //     console.log('Event: Wrong input', e.detail.captchaId);
                // });
            });
        </script>

        <!--
            Script to submit the form(s) with Ajax.

            NOTE: If you want to use FormData instead of .serialize(), make sure to
            include the inputs 'ic-rq', 'ic-wid', 'ic-cid' and 'ic-hp' into your FormData object.
            Take a look at the commented code down below.
        -->
        <script type="text/javascript">
            $(document).ready(function () {
                $('form').submit(function (e) {
                    e.preventDefault();

                    // Get the form element.
                    const form = $(this);

                    // Perform the AJAX call.
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action'),
                        data: form.serialize()
                    }).done(function (data) {
                        $('.message').html(data);
                    }).fail(function () {
                        console.log('Error: Failed to submit form.')
                    });

                    // // FormData example:
                    //
                    // // Get the form element.
                    // const form = $(this);
                    //
                    // // Build the FormData object.
                    // const formData = new FormData();
                    // formData.append('ic-rq', form.find('input[name="ic-rq"]').val());
                    // formData.append('ic-wid', form.find('input[name="ic-wid"]').val());
                    // formData.append('ic-cid', form.find('input[name="ic-cid"]').val());
                    // formData.append('ic-hp', form.find('input[name="ic-hp"]').val());
                    //
                    // // Perform the AJAX call.
                    // $.ajax({
                    //     type: 'POST',
                    //     url: form.attr('action'),
                    //     data: formData,
                    //     processData: false,
                    //     contentType: false
                    // }).done(function (data) {
                    //     $('.message').html(data);
                    // }).fail(function () {
                    //     console.log('Error: Failed to submit form.')
                    // });
                });
            });
        </script>
    </body>
</html>
