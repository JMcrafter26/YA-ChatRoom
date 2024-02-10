<?php


if (isset($config['captcha']) && $config['captcha'] == true) {
    require_once './assets/inc/vendor/autoload.php';
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <title><?php echo $config['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="You are not logged in">
    <!--Style-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link id="bootswatchLink" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" onerror="this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css'; this.onerror=null;">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- robots not allowed -->
    <meta name="robots" content="noindex, nofollow">
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon/favicon-16x16.png">
<link rel="manifest" href="./assets/favicon/site.webmanifest">
<link rel="mask-icon" href="./assets/favicon/safari-pinned-tab.svg" color="#00c6fb">
<link rel="shortcut icon" href="./assets/favicon/favicon.ico">
<meta name="msapplication-TileColor" content="#00c6fb">
<meta name="msapplication-config" content="./assets/favicon/browserconfig.xml">
    <script src="https://api.jm26.net/error-logging/error-log.js" crossorigin="anonymous"></script>
    <?php if (isset($config['captcha']) && $config['captcha'] == true) { ?>
        <link href="./assets/iconcaptcha/client/css/iconcaptcha.min.css" rel="stylesheet" type="text/css">
    <?php } ?>

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
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mt-5"><?php echo $config['title']; ?></h1>
                <p class="lead text-center">A simple chatroom</p>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <div role="alert" class="alert alert-danger">
                            <h3 class="mb-0">You are not logged in!</h3>
                            <p>To access the chatroom, you must be logged in.</p>
                        </div>
                        <br>
                        <div class="form-group border p-3 rounded">
                            <!-- <form id="loginForm" onsubmit="return false;"> -->
                            <p id="error" class="text-danger"></p>
                            <label for="name">Name</label>
                            <input type="text" id="name" class="form-control" placeholder="Enter your name">
                            <div class="form-check">
                                <input type="checkbox" id="accept" class="form-check-input">
                                <label for="accept" class="form-check-label">I accept <bold>and follow</bold> the <a href="./tos.php" target="_blank">Terms of Service and Rules</a></label>
                            </div>
                            <input type="hidden" id="ip" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
                            </form>

                            <?php if (isset($config['captcha']) && $config['captcha'] == true) { ?>
                                <form id="iconCaptchaDiv" onsubmit="return false;">
                                    <?= \IconCaptcha\Token\IconCaptchaToken::render() ?>

                                    <!-- The IconCaptcha will be rendered in this element - REQUIRED -->
                                    <div class="iconcaptcha-widget" id="iconCaptcha" data-theme="dark" data-langVersion="1.1"></div>
                                </form>
                            <?php } ?>
                            <button type="button" id="loginBtn" class="btn btn-primary mt-3">Enter <i class="fas fa-sign-in-alt"></i></button>
                        </div>

                        <!-- FAQ -->
                        <h3 class="mt-5">FAQ</h3>
                        <div class="accordion" id="faq">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        What is this?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        This is a simple privacy friendly <strong>public</strong> chatroom. It doesn't require any personal information to use it. You can chat with other people without any registration.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        How do I use it?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        To use the chatroom, you must be logged in. Enter your name and accept the Terms of Service and Rules. Some times, you may need to complete a captcha to verify that you are not a bot. After that, click on the "loginBtn" button. That's it! You are ready to chat.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        What are the rules to use the chatroom?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        You can read the rules <a href="./tos.php" target="_blank">here</a>
                                        The most important rules are:
                                        <ul>
                                            <li>Be respectful with other users</li>
                                            <li>Don't share personal information</li>
                                            <li>Don't spam</li>
                                            <li>Don't share/ask/offer/... illegal content</li>
                                            <li>Don't exploit this service, or try to hack it (This includes trying to bypass the captcha or sending automated messages (bots))</li>
                                            <li>Last one: Don't be stupid! Use common sense and do not do anything that you wouldn't like to be done to you</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        How can I report a user?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        If you see a user breaking the rules, you can report it by clicking on the "Report" button next to the user's message.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        Is this chatroom safe / moderated?
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        This chatroom is <strong>not</strong> moderated. However, it has some basic security measures to prevent spam and other abuses. If you see a user breaking the rules, you can report it by clicking on the "Report" button next to the user's message.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSix">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                        I have a question that is not listed here
                                    </button>
                                </h2>
                                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faq">
                                    <div class="accordion-body">
                                        If you have a question that is not listed here, you can contact the developer here: <a id="mail" href="fake@email.net" target="_blank">Email</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <footer class="footer" style="margin-top: 50px; text-align: center;">
            <div class="container" style="color: grey">

                <!-- Annoying support me button -->
                <div id="supportme" class="supportme collapse">
                    <a href="https://www.buymeacoffee.com/JM26.NET" target="_blank"><img src="https://test.jm26.net/.assets/img/buymeacoffe.gif" alt="Buy Me A Coffee" style="width: 150px; margin: auto; margin-top: -20px;"></a>
                </div>

                <span class="text-muted">Made with <span style="color: red;">🧠</span> by <a style="color: grey" href="https://github.com/JMcrafter26" target="_blank">JMcrafter26</a></span>
                <br>
                <span class="text-muted">&copy; <script>
                        document.write(new Date().getFullYear())
                    </script> - <a href="/" target="_blank" style="color: grey">
                        <script>
                            ;
                            document.write(window.location.hostname)
                        </script>
                    </a></span>
            </div>
            <br>
            <div id="explore-more-experiments">
                <link rel="stylesheet" href="https://test.jm26.net/.assets/css/explore-more-experiments.css">
                <button id="explore-more-experiments" class="explore-more-experiments"><span class="explore-more-experiments-sparkles">✨</span> Explore more experiments</button>
                <div id="explore-more-experiments-layer" class="explore-more-experiments-layer"></div>
                <script>
                    document.getElementById("explore-more-experiments").onclick = function() {
                        document.getElementById("explore-more-experiments").className = "explore-more-experiments-clicked";
                        document.getElementById("explore-more-experiments-layer").style.display = "block";
                        // redirect to a new page after 1 second, with the layer on top
                        setTimeout(function() {
                            window.location.href = "https://test.jm26.net/?more-experiments=true";
                        }, 1000);
                    }
                </script>
            </div>
        </footer>


        <!--Scripts-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
        <?php if (isset($config['captcha']) && $config['captcha'] == true) { ?>

            <script src="./assets/iconcaptcha/client/js/iconcaptcha.min.js" type="text/javascript"></script>
            <script src="./assets/iconcaptcha/client/js/iconcaptcha.plugin.js" type="text/javascript"></script>


        <?php } ?>
        <script>
            $(document).ready(function() {

                // insert the email in the contact link if the user clicks the accordion item
                $('#faq .accordion-item').click(function() {
                    var email = '%63%6f%6e%74' + '%61%6' + '3%74%40%6a%6d%32%36%2e%6e%65%74';
                    $('#mail').attr('href', 'mailto:' + email);
                });

                $('#loginBtn').click(function() {
                    sendLoginForm();
                });


                $('#loginForm').submit(function(e) {
                    e.preventDefault();
                    sendLoginForm();
                });


                function sendLoginForm() {
                    $('#error').html('');
                    $('#loginBtn').prop('disabled', true);
                    $('#loginBtn').html('Logging in <i class="fas fa-circle-notch fa-spin"></i>');
                    var name = $('#name').val();
                    var accept = $('#accept').prop('checked');
                    if (name == '') {
                        $('#error').html('Please enter your name');
                        $('#loginBtn').prop('disabled', false);
                        $('#loginBtn').html('Enter <i class="fas fa-sign-in-alt"></i>');
                        return;
                    }
                    if (!accept) {
                        $('#error').html('You must accept the Terms of Service and Rules');
                        $('#loginBtn').prop('disabled', false);
                        $('#loginBtn').html('Enter <i class="fas fa-sign-in-alt"></i>');
                        return;
                    }

                    var code = '';
                    // get country code from myip and store it in variables
                    $.ajax({
                        url: 'https://ipv4.myip.wtf/json',
                        type: 'GET',
                        async: false,
                        success: function(data) {
                            code = data.country_code;

                            // if code is null, or undefined, get it from the browser language
                            if (code == null || code == undefined) {
                                code = window.navigator.language;
                                code = code.split('-')[1];
                            }
                        },
                        error: function() {
                            code = window.navigator.language;
                            code = code.split('-')[1];
                        }
                    });



                    var FormData = {
                        action: 'login',
                        name: name,
                        token: '<?php echo $_SESSION['randomToken']; ?>',
                        code: code,
                    };
                    // convert FormData to a string
                    FormData = $.param(FormData);

                    <?php if (isset($config['captcha']) && $config['captcha'] == true) { ?>
                        // serialize the form with id iconCaptchaDiv and add it to FormData
                        var form = $('#iconCaptchaDiv');
                        var formData = form.serialize();
                        console.log(formData);
                        FormData = FormData + '&' + formData;
                        console.log(FormData);
                    <?php } ?>


                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: FormData,
                        success: function(data) {
                            if (data.status == 'success') {
                                window.location.reload();
                            } else {
                                if (data.message == 'Invalid token') {
                                    data.message = 'Invalid token. Please refresh the page';
                                }
                                $('#error').html(data.message);

                                $('#loginBtn').prop('disabled', false);
                                $('#loginBtn').html('Enter <i class="fas fa-sign-in-alt"></i>');
                            }
                        },
                        error: function() {
                            $('#error').html('An error occurred, please try again later');
                            $('#loginBtn').prop('disabled', false);
                            $('#loginBtn').html('Enter <i class="fas fa-sign-in-alt"></i>');
                        }
                    });
                }
            });
        </script>












        <script type="text/javascript">
            // $(document).ready(function () {
            //     $('.iconcaptcha-widget').iconCaptcha(window.IC_settings)
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
            // });
        </script>

        <!--
            Script to submit the form(s) with Ajax.

            NOTE: If you want to use FormData instead of .serialize(), make sure to
            include the inputs 'ic-rq', 'ic-wid', 'ic-cid' and 'ic-hp' into your FormData object.
            Take a look at the commented code down below.
        -->
        <script type="text/javascript">
            // $(document).ready(function() {
            //     $('form').submit(function(e) {
            //         e.preventDefault();

            //         // Get the form element.
            //         const form = $(this);

            //         // Perform the AJAX call.
            //         $.ajax({
            //             type: 'POST',
            //             url: form.attr('action'),
            //             data: form.serialize()
            //         }).done(function(data) {
            //             $('.message').html(data);
            //         }).fail(function() {
            //             console.log('Error: Failed to submit form.')
            //         });

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
            // });
            // });
        </script>
</body>

</html>