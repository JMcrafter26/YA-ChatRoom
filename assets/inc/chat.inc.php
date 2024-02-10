<?php
// session_start();
require_once 'config.php';

$error = array(
    'token' => $_SESSION['token'],
    'name' => $_SESSION['name'],
    'randomToken' => $_SESSION['randomToken'],
);

// header('Content-Type: application/json');
// die(json_encode($error));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <title>Chatroom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A simple chatroom">
    <!--Style-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link id="bootswatchLink" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" onerror="this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css'; this.onerror=null;">
    <meta name="theme-color" content="var(--bs-bg-body)">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css" />
    <script src="https://api.jm26.net/error-logging/error-log.js" crossorigin="anonymous"></script>

    <link rel="apple-touch-icon" sizes="180x180" href="./assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="./assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="./assets/favicon/safari-pinned-tab.svg" color="#00c6fb">
    <link rel="shortcut icon" href="./assets/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#00c6fb">
    <meta name="msapplication-config" content="./assets/favicon/browserconfig.xml">

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

        window.config = {
            token: '<?php echo $_SESSION['token']; ?>',
            name: '<?php echo $_SESSION['name']; ?>',
        };

        var vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    </script>
    <link rel='stylesheet' href='assets/css/chat.css'>
</head>

<body>
    <div class="container-fluid mt-2">
        <div class="row">
            <!-- only on mobile hide member list and show a button to open it -->
            <!-- button to open member list -->
            <div class="col-12 d-lg-none d-flex justify-content-between align-items-center mb-3" style="margin-bottom: -20px;">
                <button class="btn btn-primary position-relative" type="button" data-bs-toggle="collapse" data-bs-target="#memberList" aria-expanded="false" aria-controls="memberList">
                    <i class="fas fa-users"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="userCountMobile">1</span>
                    <span class="visually-hidden">Members</span>
                    </span>
                </button>
                <h1 class="fs-5 user-select-none"><?php echo $config['title']; ?></h1>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fas fa-cog"></i></button>
            </div>

            <div class="col-lg-3 collapse d-lg-block" id="memberList">
                <!-- Member List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title user-select-none">Members <span class="badge bg-secondary" id="userCount"></span></h5>
                    </div>
                    <div class="card-body">
                        <!-- your name and settings -->
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="margin-top: -10px;">
                            <span class="badge bg-success" id="yourStatus">You</span><span class="fw-bold"><?php echo $_SESSION['name']; ?></span>
                            <button class="btn btn-sm btn-outline-secondary d-none d-lg-block" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fas fa-cog"></i></button>
                        </div>

                        <ul class="list-group list-group-flush overflow-auto" style="height: 70vh;" id="users">
                            <div class="d-flex align-items-center justify-content-center" style="height: 100%;">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </ul>
                    </div>
                </div>
                <!-- Footer -->
                <footer class="mt-2 text-center text-muted card d-flex justify-content-center align-items-center pt-2 d-none d-lg-block" style="font-size: 0.8rem; height: 8vh;">
                    &copy; <?php echo date('Y'); ?> <a href="./" class="text-muted"><?php echo $config['title']; ?></a>
                    <p class="text-muted user-select-none" style="font-size: 0.5rem;">By using this chatroom you agree to our <a href="#" class="text-muted">Terms of Service</a></p>
                </footer>
            </div>




            <div class="col-lg-9">
                <!-- Chat -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title user-select-none">Chat</h5>
                    </div>
                    <div class="card-body">
                        <!-- Chat messages, scrollable -->
                        <div class="list-group list-group-flush overflow-auto" style="height: 70.5vh;" id="messages">
                            <div class="d-flex align-items-center justify-content-center" style="height: 100%;">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Chatbox -->
                <div class="card mt-3">
                    <div class="card-body">
                        <form id="chatForm">
                            <div class="mb-1" id="chatFormDiv">
                                <p id="error" class="text-danger" style="font-size: 0.8rem; margin-top: -10px; margin-bottom: 10px; min-height: 1.2rem;">This is a public chatroom. Do not share personal information.</p>

                                <div class="input-group">
                                    <input type="text" class="form-control" id="messageArea" placeholder="Message">
                                    <button type="button" class="btn btn-primary" id="sendBtn"><i class="fas fa-paper-plane"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Settings Modal -->
    <div id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true" data-bs-keyboard="false" class="modal fade">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 user-select-none" id="settingsModalLabel">Settings</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active user-select-none" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance" aria-selected="true"><i class="fas fa-paint-brush"></i> Appearance</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link user-select-none" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false"><i class="fas fa-shield-alt"></i> Security</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link user-select-none" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="false"><i class="fas fa-info-circle"></i> About</button>
                            </li>
                        </ul>
                        <div class="tab-content m-3" id="settingsTabsContent">
                            <div class="tab-pane fade show active" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="darkModeSwitch">
                                    <label class="form-check-label" for="darkModeSwitch"><i class="fas fa-moon"></i> Dark Mode</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="faviconCountSwitch" checked>
                                    <label class="form-check-label" for="faviconCountSwitch"><i class="fas fa-eye"></i> Show unread message count in favicon</label>
                                </div>
                                <!-- select bootswatch theme open modal button -->
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#themeModal" id="themeBtn">Change theme <i class="fas fa-palette"></i></button>

                            </div>
                            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                <p class="text-danger">Do not change these settings if you don't know what you are doing.</p>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="hideSpamSwitch" checked>
                                    <label class="form-check-label" for="hideSpamSwitch"><i class="fas fa-ban"></i> Hide spam messages (<span id="spamCount">0</span> spam messages detected)</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="showUrlWarningSwitch" checked>
                                    <label class="form-check-label" for="showUrlWarningSwitch"><i class="fas fa-exclamation-triangle"></i> Show warning before opening external links</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="sendBusyStatusSwitch" checked>
                                    <label class="form-check-label" for="sendBusyStatusSwitch"><i class="fas fa-user-clock"></i> Send busy status (you are busy if tab is not focused)</label>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
                                <p>This is a simple chatroom. It is not secure. Do not share personal information.</p>
                                <p>Created by <a href="https://jm26.net" target="_blank" rel="noopener noreferrer">jm26</a></p>
                                <p>Source code on <a href="#" target="_blank" rel="noopener noreferrer">GitHub</a></p>

                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <!-- logout button on the left -->
                        <button type="button" class="btn btn-danger" id="logoutBtn">Logout <i class="fas fa-sign-out-alt"></i></button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Modal -->
    <div class="modal fade" id="themeModal" tabindex="-1" aria-labelledby="themeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="themeModalLabel">Change theme</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-outline-secondary mb-1 useTheme" data-theme="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">Reset to default</button>
                    <p>Choose a theme from <a href="https://bootswatch.com" target="_blank" rel="noopener noreferrer">Bootswatch</a>:</p>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> The theme will be saved in your browser. It will not be shared with other users.</p>
                    <p class="text-muted">Hover or click an image to have a closer look.</p>
                    <hr>
                    <div class="d-flex justify-content-between flex-wrap" id="themeList">
                        <div class="card placeholder-glow" style="width: 15rem;">
                            <img src="" class="card-img-top placeholder" alt="..." style="height: 10rem; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title placeholder">Theme name</h5>
                                <p class="card-text placeholder">Theme description</p>
                                <button type="button" class="btn btn-primary placeholder">Use this theme</button>
                            </div>
                        </div>
                        <div class="card placeholder-glow" style="width: 15rem;">
                            <img src="" class="card-img-top placeholder" alt="..." style="height: 10rem; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title placeholder">Theme name</h5>
                                <p class="card-text placeholder">Theme description</p>
                                <button type="button" class="btn btn-primary placeholder">Use this theme</button>
                            </div>
                        </div>
                        <div class="card placeholder-glow" style="width: 15rem;">
                            <img src="" class="card-img-top placeholder" alt="..." style="height: 10rem; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title placeholder">Theme name</h5>
                                <p class="card-text placeholder">Theme description</p>
                                <button type="button" class="btn btn-primary placeholder">Use this theme</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Url Warning Modal -->
    <!-- <div class="modal fade" id="urlWarningModal" tabindex="-1" aria-labelledby="urlWarningModalLabel" aria-hidden="true"> -->
    <div class="modal fade" id="urlWarningModal" tabindex="-1" aria-labelledby="urlWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="urlWarningModalLabel">Warning</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h1 class="text-danger text-center" style="font-size: 70px;"><i class="fas fa-exclamation-triangle"></i></h1>
                    <h2 class="text-center">You are about to open an external link</h2>
                    <p class="text-danger fw-bold ">Links can be dangerous. They can lead to phishing sites or malware. <a href="https://www.phishing.org/10-ways-to-avoid-phishing-scams" target="_blank" rel="noopener noreferrer">Learn more</a></p>
                    <p>Only click on links from people you really trust.</p>
                    <!-- url and and favicon from google -->
                    <img src="" alt="Favicon" id="urlFavicon" class="img-fluid mb-3 d-flex mx-auto rounded" height="32" width="32">
                    <input type="text" class="form-control" id="urlInput" readonly value="https://example.com">
                </div>
                <div class="modal-footer justify-content-between">
                    <!-- never show again button on the left -->
                    <button type="button" class="btn btn-outline-secondary" id="neverShowAgainBtn">Never show again</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="openUrlBtn">Open <i class="fas fa-external-link-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- captcha modal, do not allow to close it -->
    <div id="captchaModal" tabindex="-1" aria-labelledby="captchaModalLabel" aria-hidden="true" class="modal fade" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="captchaModalLabel">Complete the captcha</h1>
                </div>
                <div class="modal-body">
                    <p class="text-center">To prevent spam, please complete the captcha.</p>
                    <div class="d-flex justify-content-center align-items-center">
                        <div id="iconCaptcha" class="w-100">
                            <iframe src="" frameborder="0" width="100%" height="100%" id="iconCaptchaIframe"></iframe>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="reloadCaptchaBtn">Reload</button>
                    </div>
                </div>
            </div>
        </div>

        <!--Scripts-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
        
        <script>
            // set the vh to the actual height
            function setVh() {
                // on mobile, remove 1vh to fix the address bar issue
                if (window.innerWidth < 992) {
                    console.log('mobile');
                    var vh = window.innerHeight * 0.01 - 10;
                    document.documentElement.style.setProperty('--vh', `${vh}px`);
                    // dynamicly set the height of the user list
                    document.getElementById('users').style.height = 'auto';
                    // set mb-3 to the user list
                    document.getElementById('memberList').classList.add('mb-3');
                    document.getElementById('chatFormDiv').classList.remove('mb-1');

                    // make messages list variable height so that the entire page is filled and doesn't scroll, and the chatbox is always at the bottom
                    // get the height of the chatbox
                    var chatboxHeight = document.getElementById('chatFormDiv').offsetHeight;
                    document.getElementById('messages').style.height = `calc(90vh - 8vh - 8vh - ${chatboxHeight}px)`;

                    // disable zooming
                    document.addEventListener('touchmove', function(event) {
                        if (event.scale !== 1) {
                            event.preventDefault();
                        }
                    }, {
                        passive: false
                    });
                } else {

                    var vh = window.innerHeight * 0.01;
                    document.documentElement.style.setProperty('--vh', `${vh}px`);
                    // set the height of the message list to 70.5vh
                    document.getElementById('messages').style.height = '70.5vh';
                    // set the height of the user list to 70vh
                    document.getElementById('users').style.height = '70vh';
                    // remove mb-3 from the user list
                    document.getElementById('memberList').classList.remove('mb-3');
                    document.getElementById('chatFormDiv').classList.add('mb-1');

                    // enable zooming
                    document.addEventListener('touchmove', function(event) {
                        if (event.scale !== 1) {
                            event.preventDefault();
                        }
                    }, {
                        passive: true
                    });
                }
            }
            window.addEventListener('resize', setVh);
            setVh();
        </script>
        <script src="https://cdn.jsdelivr.net/gh/cure53/DOMPurify@main/dist/purify.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/ejci/favico.js/favico.min.js"></script>

        <script src="./assets/js/main.js"></script>
</body>

</html>