<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <title><?php
            require_once '../../config.php';
            echo $config['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="You are not logged in">
    <!--Style-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link id="bootswatchLink" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" onerror="this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css'; this.onerror=null;">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- robots not allowed -->
    <meta name="robots" content="noindex, nofollow">
    <script src="https://api.jm26.net/error-logging/error-log.js" crossorigin="anonymous"></script>
    <link href="./client/css/iconcaptcha.min.css" rel="stylesheet" type="text/css">
    <script>
        function loadTheme() {
            var theme = localStorage.getItem('bs-theme');
            if (!theme) {
                // get system theme
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            return theme;
        }

        function setTheme(theme) {
            localStorage.setItem('bs-theme', theme);
            document.querySelector('html').setAttribute('data-bs-theme', theme);

            // load settings from localstorage chat-settings array
            var settings = JSON.parse(localStorage.getItem('chat-settings'));
            if (settings) {
                // load the theme
                if (settings.bsTheme != undefined) {
                    document.getElementById('bootswatchLink').href = settings.bsTheme;
                }
            }
        }

        function initTheme() {
            var theme = loadTheme();
            setTheme(theme);
        }

        initTheme();
    </script>
    <style>
        body {
            background-color: transparent !important;
            background-image: none !important;
        }

        #iconCaptchaDiv {
            /* align to the center, without using flexbox */
            display: grid;
            place-items: center;
            min-width: 320px;
            /* width: 100%; */
            margin: 0 auto;
            background-color: transparent !important;
        }
    </style>
</head>

<body>

    <div class="container" style="height: 100vh">

        <?php
        session_start();
        if (isset($config['captcha']) && $config['captcha'] == true) {
            require_once '../inc/vendor/autoload.php';
        } else {
            die('Captcha is disabled');
        }

        // check if this is an iframe request
        if ($_SERVER['HTTP_SEC_FETCH_DEST'] != 'iframe') {
            die('Invalid request');
        }

        if (!isset($_GET['token']) || !isset($_SESSION['token']) || $_GET['token'] != $_SESSION['token']) {
            // die('Invalid token: cause: [' . $_GET['token'] . '] session: [' . $_SESSION['token'] . ']');
            die('Invalid token');
        }
        ?>

        <form id="iconCaptchaDiv" onsubmit="return false;">
            <?= \IconCaptcha\Token\IconCaptchaToken::render() ?>

            <!-- The IconCaptcha will be rendered in this element - REQUIRED -->
            <div class="iconcaptcha-widget" id="iconCaptcha" data-theme="dark" data-langVersion="1.1"></div>
        </form>
        <div role="alert" id="success" class="alert alert-success" style="display: none;"></div>
        <div role="alert" id="error" class="alert alert-danger" style="display: none;"></div>
    </div>

    <!--Scripts-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
    <script src="./client/js/iconcaptcha.min.js" type="text/javascript"></script>
    <script>
        window.IC_settings = {
            general: {
                endpoint: '../inc/captcha-request.php',
                fontFamily: 'inherit',
            },
            security: {
                interactionDelay: 1000,
                hoverProtection: true,
                displayInitialMessage: true,
                initializationDelay: 500,
                incorrectSelectionResetDelay: 3000,
                loadingAnimationDuration: 1000,
            }
        };
    </script>
    <script src="./client/js/iconcaptcha.plugin.js" type="text/javascript" data-iframe="true"></script>
    <script>
        function resizeIframe() {
            // if device width is less than 320px, zoom out, so the captcha is visible (calculate how much to zoom out)
            if (window.innerWidth < 320) {
                var zoom = window.innerWidth / 350;
                document.body.style.zoom = zoom;
            }
        }

        window.addEventListener('resize', resizeIframe);
        resizeIframe();


        function sendCaptcha() {
            var form = $('#iconCaptchaDiv');
            var formData = form.serialize();
            formData += '&action=verify';
            $.ajax({
                url: '../../api.php',
                type: 'POST',
                data: formData,
                success: function(data) {
                    if (data.status == 'success') {
                        $('#success').html(data.message + '. This window will close automatically');
                        $('#success').show();
                        $('#error').html('');
                        $('#error').hide();
                        // hide the captcha
                        $('#iconCaptcha').hide();
                        // close the iframe
                        window.parent.postMessage('close', '*');
                    } else {
                        $('#error').html(data.message);
                        $('#error').show();
                    }
                },
                error: function() {
                    $('#error').html('An error occurred');
                    $('#error').show();
                }
            });
        }

        $(document).ready(function() {
            IconCaptcha.bind('success', function(e) {

                sendCaptcha();
            });
        });


    </script>