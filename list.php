<?php
/**
 * Yet Another Directory Lister (YADL)
 * A simple PHP script (made in 2 days) to list the contents of a directory
 *
 * @version 1.5.1
 * @author JM26
 *
 * @link https://github.com/JMcrafter26/ya-directory-lister
 * @license MIT
 */
// ----- CONFIGURATION -----
// Page settings
$config = array(
    // Display settings
    'title' => 'Directory Listener',
    'description' => 'Contents of',
    'logo' => 'https://avatars.githubusercontent.com/u/77780772?v=4', // path to the logo, e.g. 'img/logo.png' or 'https://example.com/logo.png'
    'footer' => 'Directory Listener by <a href="https://go.jm26.net/github">JMcrafter26</a>',
    'showReadme' => true, // show the README.md file if it exists
    'showHeader' => true, // show the header
    'showFooter' => true, // show the footer
    'showSearch' => true, // show the search bar to search for files and directories
    'openLinksInNewTab' => false, // open links in a new tab
    'theme' => 'auto', // auto, light, dark
    'listStyle' => 'grid', // list, grid
    'pjax' => true, // enable pjax (responsive and faster page loading) BETA FEATURE! (Search does not work properly with pjax)
    'pace' => false, // enable pace (loading bar), only works if pjax is enabled


    // Path settings
    // 'path' => __dir__ . '/some/custom/path/',
    // 'path' => '/ffmpeg/', (optional, if not set, the current directory will be used)


    // Security settings
    'requirePassword' => false, // require a password to access the page
    'password' => '5f4dcc3b5aa765d61d8327deb882cf99', // (md5 hash of) the password, e.g. 'password' -> '5f4dcc3b5aa765d61d8327deb882cf99'. Use: https://www.md5hashgenerator.com/ to generate the hash
    'expirePassword' =>  60 * 60, // 1 hour (in seconds) (0 to disable), the time the password is valid (in seconds)
    'maxAttempts' => 3, // the maximum amount of attempts to enter the password before the user is locked out (0 to disable)

    // Show files and directories
    'showFiles' => true, // show files
    'showDirectories' => true, // show directories
    'showHidden' => false, // show hidden files and directories (files and directories starting with a dot)
    'respectPermissions' => true, // doesn't show files and directories that the user can't read
    'allowChangeDirectory' => true, // allow the user to change the directory by clicking on a directory (BE CAREFUL, THIS CAN BE A SECURITY RISK!)
);
// exclude files, folders and extensions
$exclude = array(
    'files' => array(
        'list.php',
        '.htaccess',
        'config.php',
    ),
    'directories' => array(
        'admin',
        'dashboard',
        'vendor',
        'node_modules',
        'bower_components',
        'dist',
        'build',
        'out',
        'target',
        'cache',
        'logs',
        'tmp',
        'temp',
        'backup',
        'backups',
    ),
    'extensions' => array(
        'log',
        'bak'
    )
);
// Advanced filetypes
$filetypes = array(
    "word" => array("doc", "docx", "wks", "wps", "wpd"),
    "excel" => array("xls", "xlsx", "ods", "csv"),
    "powerpoint" => array("ppt", "pptx", "odp"),
    "pdf" => array("pdf"),
    "video" => array("mp4", "webm", "ogg", "avi", "mov", "flv", "wmv", "mkv"),
    "audio" => array("mp3", "wav", "ogg", "flac", "m4a", "wma", "aac"),
    "image" => array("jpg", "jpeg", "png", "gif", "bmp", "svg", "webp", "ico"),
    "text" => array("txt", "md", "odt", "rtf", "tex"),
    "archive" => array("zip", "rar", "7z", "tar", "gz", "bz2", "xz", "iso"),
    "csv" => array("csv"),
    "code" => array("html", "css", "js", "php", "py", "java", "c", "cpp", "h", "hpp", "cs", "vb", "ts", "json", "xml", "sql", "sh", "bat", "ps1", "cmd", "psm1", "psd1", "ps1xml", "clixml", "cdxml", "mof", "mfl", "java", "pl", "pm", "t", "r", "rmd", "swift", "go", "rb", "lua", "kt", "dart", "coffee", "ts", "jsx", "tsx"),
    "other" => array(""),
);
// Advanced filetypes icons
$filetypesIcons = array(
    "word" => "fa-file-word",
    "excel" => "fa-file-excel",
    "powerpoint" => "fa-file-powerpoint",
    "pdf" => "fa-file-pdf",
    "video" => "fa-file-video",
    "audio" => "fa-file-audio",
    "image" => "fa-file-image",
    "text" => "fa-file-alt",
    "archive" => "fa-file-archive",
    "csv" => "fa-file-csv",
    "code" => "fa-file-code",
    "other" => "fa-file",
);
// ----- END CONFIGURATION -----
// you can edit the configuration above this line, but be careful not to break the script
$pathNotFound = false;
// remove the script directory from the path
$scriptPath = $_SERVER['SCRIPT_NAME'];
// remove the script name, so only the path remains
$scriptPath = substr($scriptPath, 0, strrpos($scriptPath, '/') + 1);

// check if requirePassword is enabled and the password is a valid md5 hash
if ($config['requirePassword']) {
    $auth = false;
    if (!isset($config['password']) || $config['password'] == '' || strlen($config['password']) != 32) {
        die('The password is not set or not a valid md5 hash');
    }

    session_start();
    if (!isset($_SESSION['YADL_attempts'])) {
        $_SESSION['YADL_attempts'] = 0;
    }
    if (!isset($_SESSION['YADL_pswdHash']) || !isset($_SESSION['pswdExpire'])) {
        $_SESSION['YADL_pswdHash'] = '';
        $_SESSION['pswdExpire'] = 0;
    } else {
        if ($_SESSION['YADL_pswdHash'] == $config['password']) {
            if ($config['expirePassword'] == 0) {
                $auth = true;
            } else {
                if ($_SESSION['pswdExpire'] < time()) {
                    $_SESSION['YADL_pswdHash'] = '';
                    $_SESSION['pswdExpire'] = 0;
                } else {
                    $auth = true;
                }
            }
        }
    }
    if (isset($_POST['password'])) {


        if (md5($_POST['password']) == $config['password']) {
            $_SESSION['YADL_pswdHash'] = md5($_POST['password']);
            $_SESSION['pswdExpire'] = time() + $config['expirePassword'];
            $auth = true;
        } else {
            $_SESSION['YADL_attempts']++;
        }

        if ($config['maxAttempts'] != 0 && $_SESSION['YADL_attempts'] >= $config['maxAttempts']) {
            $auth = false;
        }
    }

    if (!$auth) {
        // disable all options if the password is not correct
        $config['showHeader'] = false;
        $config['showFooter'] = false;
        $config['showSearch'] = false;
        $config['showReadme'] = false;
        $config['showFiles'] = false;
        $config['showDirectories'] = false;
        $config['allowChangeDirectory'] = false;
    }
} else {
    $auth = true;
}


if ($config['allowChangeDirectory']) {
    if (isset($_GET['path']) && $_GET['path'] != '' && $_GET['path'] != '/') {
        // sanitize the input

        // check if the directory the user wants to go to is not in the exclude list
        if (in_array($_GET['path'], $exclude['files']) || in_array($_GET['path'], $exclude['extensions'])) {
            $_GET['path'] = '';
        }
        $_GET['path'] = str_replace('\\', '/', $_GET['path']);
        $_GET['path'] = rtrim($_GET['path'], '/');
        $_GET['path'] = $_GET['path'] . '/';
        $_GET['path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_GET['path']);

        // header('Content-Type: application/json');
        // die(json_encode($_SERVER));




        // if the first character is not a slash, add it
        if ($_GET['path'] != '' && $_GET['path'][0] != '/') {
            $_GET['path'] = '/' . $_GET['path'];
        }



        // check if path exists
        if (!file_exists(__DIR__ . $_GET['path']) && !file_exists(__DIR__ . '/' . $_GET['path'])) {
            // if the path is the path the scipt is in
            if ($_GET['path'] == $scriptPath) {
                $_GET['path'] = '/';
            } else {
                // $_GET['path'] = __DIR__;
                // $pathNotFound = true;
                $_GET['path'] = str_replace(
                    $scriptPath,
                    '',
                    $_GET['path']
                );
                if ($_GET['path'] != '' && $_GET['path'][0] != '/') {
                    $_GET['path'] = '/' . $_GET['path'];
                }
            }
        }



        // if the path contains /../, remove it
        if (strpos($_GET['path'], '/../') !== false) {
            $_GET['path'] = '';
        }


        // if respect permissions is enabled, check if the file persmissions allow others to read the directory, e.g. (755 -> last digit is 5)
        if ($config['respectPermissions']) {
            if (substr(sprintf('%o', fileperms(__DIR__ . '/' . $_GET['path'])), -1) < 5) {
                $_GET['path'] = '';
            }
        }


        if (!isset($config['path'])) {
            $config['path'] = __DIR__;
        }



        // check if the path is outside the root (if it is up one directory, remove the last directory from the path) by using document root
        // if (strpos(__DIR__ . '/' . $_GET['path'], $_SERVER['DOCUMENT_ROOT']) === false) {
        //     $_GET['path'] = '';
        // }


        // if the directory is not empty, set the path to the new directory
        if ($_GET['path'] != '') {
            $config['path'] = $_GET['path'];
        }
    } else if (isset($_GET['path']) && $_GET['path'] == '') {
        if (!isset($config['path'])) {
            $config['path'] = __DIR__;
            $config['path'] = str_replace('\\', '/', $config['path']) . '/';
        }
    } else {
        if (!isset($config['path'])) {
            $config['path'] = __DIR__;
            $config['path'] = str_replace('\\', '/', $config['path']) . '/';
        }
    }
} else {
    if (!isset($config['path'])) {
        $config['path'] = __DIR__;
        $config['path'] = str_replace('\\', '/', $config['path']) . '/';
    }
}



// if the path is not set, use the current directory
if (!isset($config['path']) || $config['path'] == '') {
    $config['path'] = __DIR__;
    $pathNotFound = true;
} else {
    // check if the path contains the document root
    if (strpos($config['path'], $_SERVER['DOCUMENT_ROOT']) === false) {
        $config['path'] = __DIR__ . $config['path'];
    }
    if (!file_exists($config['path'])) {
        $config['path'] = __DIR__;
        $pathNotFound = true;
    } else {
        if (!is_dir($config['path'])) {
            $config['path'] = __DIR__;
            $pathNotFound = true;
        }
    }
}

$config['path'] = str_replace('\\', '/', $config['path']);
$config['path'] = rtrim($config['path'], '/');
$config['path'] = $config['path'] . '/';
$config['path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $config['path']);


// check if document root is in the raw path
if (strpos($config['path'], $_SERVER['DOCUMENT_ROOT']) === false) {
    $config['rawPath'] = $_SERVER['DOCUMENT_ROOT'] . $config['path'];
} else {
    $config['rawPath'] = $_SERVER['DOCUMENT_ROOT'] . $config['path'];
}
$config['rawPath'] = str_replace('\\', '/', $config['rawPath']);
// get the contents of the directory
$contents = scandir($config['rawPath']);
$directories = array();
$files = array();
//DEBUG: If contents items count is higher that 20, slice the array
// if (count($contents) > 20) {
// $contents = array_slice($contents, 0, 20);
// }
$pathinfo;
if (function_exists('pathinfo')) {
    try {
        $extension = pathinfo('file.txt', PATHINFO_EXTENSION);
        if ($extension == '' || $extension == null) {
            $pathinfo = false;
        } else {
            $pathinfo = true;
        }
    } catch (Exception $e) {
        $pathinfo = false;
    }
} else {
    $pathinfo = false;
}
if (!isset($exclude)) {
    $exclude = array(
        'files' => array(),
        'extensions' => array(),
    );
}
if (!isset($pathinfo)) {
    $pathinfo = false;
}
foreach ($contents as $content) {
    if ($content == '.' || $content == '..') {
        continue;
    }
    if (!$config['showHidden'] && $content[0] == '.') {
        continue;
    }
    // if respect permissions is enabled, check if the file persmissions allow others to read the file, e.g. (644, 755 -> last digit is 4 or higher)
    if ($config['respectPermissions']) {
        if (substr(sprintf('%o', fileperms($config['rawPath'] . $content)), -1) < 4) {
            continue;
        }
    }
    if (is_dir($config['rawPath'] . $content) && $config['showDirectories']) {
        // check if the directory is in the exclude list
        if (in_array($content, $exclude['directories'])) {
            continue;
        }
        $directories[count($directories)] = $content;
    } else if ($config['showFiles']) {
        // get filetype from extension, e.g. video, audio, image, text, etc.
        if ($pathinfo) {
            $extension = pathinfo($content, PATHINFO_EXTENSION);
        } else {
            // get the extension by splitting the string at the last dot (also check if the string contains a dot)
            $extension = false;
            if (strpos($content, '.') !== false) {
                $extension = explode('.', $content);
                $extension = $extension[count($extension) - 1];
            } else {
                $extension = '';
            }
        }
        // check if the file/folder/extension is in the exclude list
        if (in_array($content, $exclude['files']) || in_array($extension, $exclude['extensions'])) {
            continue;
        }
        $filetype = 'other';
        foreach ($filetypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                $filetype = $type;
                break;
            }
        }
        $files[count($files) - 1] = array(
            'name' => $content,
            'type' => $filetype,
            'fa' => $filetypesIcons[$filetype],
        );
    } else {
        continue;
    }
}
// if the arrays are empty
if (count($directories) == 0 && $config['showDirectories']) {
    $directories = false;
} else {
    sort($directories);
}
if (count($files) == 0 && $config['showFiles']) {
    $files = false;
} else {
    sort($files);
}
if ($directories == false && $files == false) {
    $noContent = true;
} else {
    $noContent = false;
}
// sort the arrays
// check if the current directory is the root directory or inside the scipt directory
if ($config['path'] != '/' && $config['path'] != $scriptPath) {
    if ($directories) {
        $directories = array_merge(array('..'), $directories);
    } else {
        $directories = array('..');
    }
}
if (!isset($directories) || empty($directories)) {
    $directories = array();
}
if (!isset($files) || empty($files)) {
    $files = array();
}
$reademe = false;
if ($config['showReadme']) {
    if (file_exists($config['rawPath'] . 'README.md')) {
        $reademe = $config['rawPath'] . 'README.md';
    } else if (file_exists($config['rawPath'] . 'readme.md')) {
        $reademe = $config['rawPath'] . 'readme.md';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $config['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $config['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Contents of">
    <!--Style-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://api.jm26.net/error-logging/error-log.js" crossorigin="anonymous"></script>
    <?php
    if ($reademe) {
    ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hyrious/github-markdown-css@gh-pages/github-markdown.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hyrious/github-markdown-css@gh-pages/light.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hyrious/github-markdown-css@gh-pages/dark.css">
    <?php }
    if (isset($config['logo']) && $config['logo'] != '') { ?>
        <link rel="icon" href="<?php echo $config['logo']; ?>">
    <?php } ?>

    <?php if ($config['allowChangeDirectory'] && $config['pjax'] && $config['pace']) { ?>
        <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pace-js@latest/pace-theme-default.min.css">
    <?php } ?>

    <style>
        <?php if ($config['listStyle'] == 'grid') { ?>.card {
            transition: all 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            background-color: var(--bs-secondary-bg);
        }

        <?php } if ($config['allowChangeDirectory'] && $config['pjax'] && $config['pace']) { ?>
            .pace .pace-progress {
            background: var(--bs-primary);
        }
        <?php } ?>
    </style>

    <script>
        // get the theme from the local storage
        var theme = localStorage.getItem('bs-theme');
        // if the theme is not set, set it to auto
        if (theme == null && document.querySelector('html').getAttribute('data-bs-theme') == 'auto') {
            // get the system theme
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            } else {
                theme = 'light';
            }
        }
        // set the theme
        if (theme != null) {
            document.querySelector('html').setAttribute('data-bs-theme', theme);
        }

        window.jsonList = {
            "directories": <?php echo json_encode($directories); ?>,
            "files": <?php echo json_encode($files); ?>,
        };

        if (window.location.search == '?path=') {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        <?php if ($config['allowChangeDirectory'] && $config['pjax']) { ?>
            document.addEventListener("ajaxify:load", function(e) {
                if (window.location.search == '?path=') {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        <?php } ?>
    </script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12 mt-1">
                <?php if ($config['showHeader']) {
                    if (isset($config['logo']) && $config['logo'] != '') { ?>
                        <img src="<?php echo $config['logo']; ?>" alt="Logo" class="img-fluid mx-auto d-block" style="width: 100px;">
                    <?php } ?>
                    <h1 class="text-center"><?php echo $config['title']; ?></h1>
                    <p class="lead text-center"><?php echo $config['description']; ?> <code><?php echo $config['path']; ?></code></p>
                    <hr>
                <?php } ?>
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12">
                                <?php if (!$auth) { ?>
                                    <div class="d-flex justify-content-center border rounded p-3 mt-2">
                                        <form method="post">
                                            <h2>Password required</h2>
                                            <?php if (isset($_POST['password']) && $_POST['password'] != '') { ?>
                                                <div class="alert alert-danger" role="alert">
                                                    The password is incorrect
                                                </div>
                                            <?php }
                                            if ($_SESSION['YADL_attempts'] >= $config['maxAttempts']) { ?>
                                                <div class="alert alert-danger" role="alert">
                                                    You have reached the maximum amount of attempts
                                                </div>
                                            <?php } else { ?>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="password" name="password" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            <?php } ?>
                                        </form>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php exit;
                                } ?>
<?php if ($config['showSearch']) { ?>
    <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search-addon">
        <button class="btn btn-outline-secondary" type="button" id="search-addon"><i class="fas fa-search"></i></button>
    </div>
<?php }
if ($pathNotFound) { ?>
    <div class="d-flex justify-content-center">
        <div class="alert alert-warning text-center w-50" role="alert">
            <p>The path provided was not found <i class="fas fa-frown"></i></p>
        </div>
    </div>
<?php
}
if ($noContent) { ?>
    <div class="alert alert-warning text-center" role="alert">
        No directories or files found
    </div>
<?php }
if ($config['showDirectories'] && $directories) { ?>
    <h2>Directories</h2>
    <?php if ($config['listStyle'] == 'grid') { ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="directories">
            <?php if ($config['allowChangeDirectory'] != true) {
                foreach ($directories as $directory) {
                    if ($directory == '..') {
                        echo '<div class="col"><a class="card text-decoration-none" href="' . $config['path'] . $directory . '" data-name="' . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . ' style="cursor: pointer;"><div class="card-body"><i class="fas fa-level-up-alt"></i> Parent Directory</div></a></div>';
                        continue;
                    }
                    echo '<div class="col"><a class="card text-decoration-none" href="' . $config['path'] . $directory . '" data-name="' . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') .
                        ' style="cursor: pointer;"><div class="card-body"><i class="fas fa-folder"></i> ' . $directory . '</div></a></div>';
                }
            } else {
                foreach ($directories as $directory) {
                    if ($directory == '..') {
                        $parent = substr($config['path'], 0, strrpos($config['path'], '/', -2));
                        echo '<div class="col"><div class="card" data-name="' . $directory . '" style="cursor: pointer;">
                                                        <div class="card-body">
                                                        <div class="d-flex justify-content-between">
                                                        <a class="text-decoration-none text-body" href="?path=' . $parent . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . '><i class="fa-solid fa-level-up-alt"></i> Parent Directory</a>
                                                        <a class="text-decoration-none text-body" href="' . $config['path'] . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . '><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                                        </div></div></div></div>';
                        continue;
                    }
                    echo '<div class="col">
                                                        <div class="card" data-name="' . $directory . '" style="cursor: pointer;">
                                                        <div class="card-body">
                                                        <div class="d-flex justify-content-between">
                                                        <a class="text-decoration-none text-body" href="?path=' . $config['path'] . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . '><i class="fa-solid fa-folder-open"></i> ' . $directory . '</a>
                                                        <a class="text-decoration-none text-body" target="' . ($config['openLinksInNewTab'] ? '_blank' : '_self') . '" href="' . $config['path'] . $directory . '"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                                        </div></div></div></div>';
                }
            }
            ?>
        </div>
        <br>
    <?php } else { ?>
        <ul class="list-group mb-3" id="directories">
            <?php if ($config['allowChangeDirectory'] != true) {
                foreach ($directories as $directory) {
                    echo '<a class="list-group-item list-group-item-action" href="' . $config['path'] . $directory . '" data-name="' . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . '><i class="fas fa-folder"></i> ' . $directory . '</a>';
                }
            } else {
                foreach ($directories as $directory) {
                    if ($directory == '..') {
                        $parent = substr($config['path'], 0, strrpos($config['path'], '/', -2));
                        echo '<div class="list-group-item list-group-item-action" data-name="' . $directory . '" style="cursor: pointer;"><div class="d-flex justify-content-between"><a class="text-decoration-none text-body" href="?path=' . $parent . '"><i class="fa-solid fa-level-up-alt"></i> Parent Directory</a><a class="text-decoration-none text-body" href="' . $parent . '"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div>';
                        continue;
                    }
                    echo '<div class="list-group-item list-group-item-action" data-name="' . $directory . '" style="cursor: pointer;">
                                                        <div class="d-flex justify-content-between">
                                                        <a class="text-decoration-none text-body" href="?path=' . $config['path'] . $directory . '" ' . ($config['openLinksInNewTab'] ? 'target="_blank"' : '') . '><i class="fa-solid fa-folder-open"></i> ' . $directory . '</a>
                                                        <a class="text-decoration-none text-body" href="' . $config['path'] . $directory . '" target="' . ($config['openLinksInNewTab'] ? '_blank' : '_self') . '"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                                        </div></div>';
                }
            }
            ?>
        </ul>
<?php }
} ?>
<?php if ($config['showFiles'] && $files) { ?>
    <h2>Files</h2>
    <?php
    if ($config['listStyle'] == 'grid') { ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="files">
            <?php
            foreach ($files as $file) {
                echo '<div class="col"><a class="card text-decoration-none" href="' . $config['path'] . $file['name'] . '" data-name="' . $file['name'] . '" target="' . ($config['openLinksInNewTab'] ? '_blank' : '_self') . '"><div class="card-body"><i class="fas ' . $file['fa'] . '"></i> ' . $file['name'] . '</div></a></div>';
            }
            ?>
        </div>
        <br>
    <?php } else {
    ?>
        <ul class="list-group" id="files">
            <?php
            foreach ($files as $file) {
                echo '<a class="list-group-item list-group-item-action" href="' . $config['path'] . $file['name'] . '" data-name="' . $file['name'] . '" target="' . ($config['openLinksInNewTab'] ? '_blank' : '_self') . '"><i class="fas ' . $file['fa'] . '"></i> ' . $file['name'] . '</a>';
            }
            ?>
        </ul>
<?php }
} ?>
</div>
<?php
if ($reademe) {
?>
    <div class="col-12 placeholder-glow border rounded p-3 mt-2">
        <h3><a href="#readme" class="text-decoration-none black"><i class="fas fa-book"></i> README.md</a></h3>
        <hr class="w-100">
        <div id="readme" class="markdown-body placeholder"><?php echo file_get_contents($reademe); ?></div>
    </div>
<?php }
if ($config['showFooter']) { ?>
    <footer class="mt-5">
        <p class="text-center"><?php echo $config['footer']; ?></p>
    </footer>
<?php } ?>
</div>
</div>
</div>
</div>
</div>
<!--Scripts-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>


<?php if ($config['allowChangeDirectory'] && $config['pjax']) { ?>
    <!-- Better Ajaxify -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/chemerisuk/better-ajaxify/dist/better-ajaxify.min.js"></script>
<?php }
if ($reademe) { ?>
    <script src="https://unpkg.com/showdown/dist/showdown.min.js"></script> <!-- Showdown -->
    <script>
        // after the page has loaded
        document.addEventListener('DOMContentLoaded', function() {
            // load the css based on the theme
            var theme = document.querySelector('html').getAttribute('data-bs-theme');
            if (theme == 'auto') {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    theme = 'dark';
                } else {
                    theme = 'light';
                }
            }
            console.log(theme);
            // if (theme == 'dark') {
            // document.getElementById('readme').classList.add('dark');
            // } else {
            // document.getElementById('readme').classList.add('light');
            // }
            var converter = new showdown.Converter();
            var text = document.getElementById('readme').innerHTML;
            // convert :emoji: to real emojis
            var html = converter.makeHtml(text);
            document.getElementById('readme').innerHTML = html;
            document.getElementById('readme').classList.remove('placeholder');
        });

        <?php if ($config['allowChangeDirectory'] && $config['pjax']) { ?>
            document.addEventListener('ajaxify:load', function() {
            var converter = new showdown.Converter();
            var text = document.getElementById('readme').innerHTML;
            var html = converter.makeHtml(text);
            document.getElementById('readme').innerHTML = html;
            document.getElementById('readme').classList.remove('placeholder');
        });
        <?php } ?>
    </script>
<?php }
if ($config['showSearch']) { ?>
    <script>
        console.log('search.js loaded');

        function search(query) {
            var directories = document.getElementById('directories');
            var files = document.getElementById('files');
            // empty the lists
            directories.innerHTML = '';
            files.innerHTML = '';
            console.log('searching for ' + query);
            // rebuild the lists
            <?php
            // check if the path is the root directory
            echo 'console.log("root directory: ' . $config['path'] . '");';
            if ($config['path'] == '/') {
                $parent = '';
            } else {
                $parent = substr($config['path'], 0, strrpos($config['path'], '/', -2));
            }
            ?>
            <?php if ($config['listStyle'] == 'list') {
                if (!$config['allowChangeDirectory']) { ?>
                    var $parent = '<?php echo $parent; ?>';
                    for (var i = 0; i < jsonList.directories.length; i++) {
                        if (jsonList.directories[i].toLowerCase().includes(query.toLowerCase())) {
                            if (jsonList.directories[i] == '..') {
                                directories.innerHTML += '<a class="list-group-item list-group-item-action" href="' + $parent + '" data-name="' + jsonList.directories[i] + '"><i class="fa-solid fa-level-up-alt"></i> Parent Directory</a>';
                            } else {
                                directories.innerHTML += '<a class="list-group-item list-group-item-action" href="' + jsonList.directories[i] + '" data-name="' + jsonList.directories[i] + '"><i class="fas fa-folder"></i> ' + jsonList.directories[i] + '</a>';
                            }
                        } else {
                            continue;
                        }
                    }
                    for (var i = 0; i < jsonList.files.length; i++) {
                        if (jsonList.files[i].name.toLowerCase().includes(query.toLowerCase())) {
                            files.innerHTML += '<a class="list-group-item list-group-item-action" href="' + jsonList.files[i].name + '" data-name="' + jsonList.files[i].name + '"><i class="fas ' + jsonList.files[i].fa + '"></i> ' + jsonList.files[i].name + '</a>';
                        } else {
                            continue;
                        }
                    }
                <?php } else { ?>
                    var $parent = '<?php echo $config['path']; ?>';
                    console.log($parent);
                    for (var i = 0; i < jsonList.directories.length; i++) {
                        if (jsonList.directories[i].toLowerCase().includes(query.toLowerCase())) {
                            if (jsonList.directories[i] == '..') {
                                directories.innerHTML += '<div class="list-group-item list-group-item-action" data-name="' + jsonList.directories[i] + '"><div class="d-flex justify-content-between"><a href="?path=' + $parent + '" class="text-decoration-none text-body"><i class="fa-solid fa-level-up-alt"></i> Parent Directory</a><a href="' + $parent + '" class="text-decoration-none"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div>';
                            } else {
                                directories.innerHTML += '<div class="list-group-item list-group-item-action" data-name="' + jsonList.directories[i] + '"><div class="d-flex justify-content-between"><a href="?path=' + gup('path') + '/' + jsonList.directories[i] + '" class="text-decoration-none text-body"><i class="fa-solid fa-folder-open"></i> ' + jsonList.directories[i] + '</a><a target="_blank" href="' + jsonList.directories[i] + '" class="text-decoration-none text-body"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div>';
                            }
                        } else {
                            continue;
                        }
                    }
                    for (var i = 0; i < jsonList.files.length; i++) {
                        if (jsonList.files[i].name.toLowerCase().includes(query.toLowerCase())) {
                            files.innerHTML += '<a class="list-group-item list-group-item-action" href="' + gup('path') + '/' + jsonList.files[i].name + '" data-name="' + jsonList.files[i].name + '"><i class="fas ' + jsonList.files[i].fa + '"></i> ' + jsonList.files[i].name + '</a>';
                        } else {
                            continue;
                        }
                    }
                <?php }
            } else {
                if (!$config['allowChangeDirectory']) {
                ?>
                    var $parent = '<?php echo $parent; ?>';
                    for (var i = 0; i < jsonList.directories.length; i++) {
                        if (jsonList.directories[i].toLowerCase().includes(query.toLowerCase())) {
                            if (jsonList.directories[i] == '..') {
                                directories.innerHTML += '<div class="col"><a class="card text-decoration-none" href="' + $parent + '" data-name="' + jsonList.directories[i] + '" style="cursor: pointer;"><div class="card-body"><i class="fas fa-level-up-alt"></i> Parent Directory</div></a></div>';
                            } else {
                                directories.innerHTML += '<div class="col"><a class="card text-decoration-none" href="' + jsonList.directories[i] + '" data-name="' + jsonList.directories[i] + '"><div class="card-body"><i class="fas fa-folder"></i> ' + jsonList.directories[i] + '</div></a></div>';
                            }
                        } else {
                            continue;
                        }
                    }
                    for (var i = 0; i < jsonList.files.length; i++) {
                        if (jsonList.files[i].name.toLowerCase().includes(query.toLowerCase())) {
                            files.innerHTML += '<div class="col"><a class="card text-decoration-none" href="' + jsonList.files[i].name + '" data-name="' + jsonList.files[i].name + '"><div class="card-body"><i class="fas ' + jsonList.files[i].fa + '"></i> ' + jsonList.files[i].name + '</div></a></div>';
                        } else {
                            continue;
                        }
                    }
                <?php } else { ?>
                    var $parent = '<?php echo $config['path']; ?>';
                    for (var i = 0; i < jsonList.directories.length; i++) {
                        if (jsonList.directories[i].toLowerCase().includes(query.toLowerCase())) {
                            if (jsonList.directories[i] == '..') {
                                directories.innerHTML += '<div class="col"><div class="card" data-name="' + jsonList.directories[i] + '" style="cursor: pointer;"><div class="card-body"><div class="d-flex justify-content-between"><a class="text-decoration-none text-body" href="?path=' + $parent + '"><i class="fa-solid fa-level-up-alt"></i> Parent Directory</a><a class="text-decoration-none text-body" target="_blank" href="' + window.location.pathname + $parent + '"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div></div></div>';
                            } else {
                                directories.innerHTML += '<div class="col"><div class="card" data-name="' + jsonList.directories[i] + '"><div class="card-body"><div class="d-flex justify-content-between"><a class="text-decoration-none text-body" href="?path=' + gup('path') + jsonList.directories[i] + '"><i class="fa-solid fa-folder-open"></i> ' + jsonList.directories[i] + '</a><a class="text-decoration-none text-body" target="_blank" href="' + window.location.pathname + jsonList.directories[i] + '"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div></div></div>';
                            }
                        } else {
                            continue;
                        }
                    }
                    for (var i = 0; i < jsonList.files.length; i++) {
                        if (jsonList.files[i].name.toLowerCase().includes(query.toLowerCase())) {
                            files.innerHTML += '<div class="col"><a class="card text-decoration-none" href="' + gup('path') + jsonList.files[i].name + '" data-name="' + jsonList.files[i].name + '"><div class="card-body"><i class="fas ' + jsonList.files[i].fa + '"></i> ' + jsonList.files[i].name + '</div></a></div>';
                        } else {
                            continue;
                        }
                    }
            <?php }
            } ?>
            if (directories.innerHTML == '') {
                directories.innerHTML = '<div class="text-center"><i class="fas fa-frown"></i> No directories found</div>';
            }
            if (files.innerHTML == '') {
                files.innerHTML = '<div class="text-center"><i class="fas fa-frown"></i> No files found</div>';
            }
        }

        document.getElementById('search-addon').addEventListener('click', function() {
            var query = document.querySelector('.form-control').value;
            search(query);
        });
        document.querySelector('.form-control').addEventListener('keyup', function() {
            var query = document.querySelector('.form-control').value;
            search(query);
        });

        function gup(name) {
            url = location.href;
            name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
            var regexS = "[\\?&]" + name + "=([^&#]*)";
            var regex = new RegExp(regexS);
            var results = regex.exec(url);
            console.log(results);
            if (results == null) {
                return '';
            } else {
                return results[1];
            }
        }

        <?php if ($config['allowChangeDirectory'] && $config['pjax']) { ?>
            document.addEventListener('ajaxify:load', function() {
                console.log('ajaxify:load');
                document.getElementById('search-addon').addEventListener('click', function() {
                    var query = document.querySelector('.form-control').value;
                    search(query);
                });
                document.querySelector('.form-control').addEventListener('keyup', function() {
                    var query = document.querySelector('.form-control').value;
                    search(query);
                });
            });
        <?php } ?>
    </script>
<?php } ?>
</body>

</html>