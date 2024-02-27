# Yet Another Chatroom

![Banner](./src/assets/banner.png)

<a href="https://github.com/JMcrafter26/yet-another-chatroom/releases" target="_blank"><img src="https://api.jm26.net/badge/beta?g&url=/github/v/release/JMcrafter26/yet-another-chatroom" height="20px" ></a>
<a href="https://github.com/JMcrafter26/yet-another-chatroom/issues" target="_blank"><img src="https://api.jm26.net/badge/beta?g&url=/github/issues/JMcrafter26/yet-another-chatroom" height="20px" ></a>
<a href=".LICENSE" target="_blank"><img src="https://api.jm26.net/badge/beta?g&url=/github/license/JMcrafter26/yet-another-chatroom" height="20px" ></a>

<!-- Slogan: A simple, privacy friendly chatroom web application. -->

This is a chatroom application that allows users to send messages to each other in real time. The application is built in plain HTML, CSS, and JavaScript, and uses PHP and SQLite for the backend.

## Table of contents

- [Yet Another Chatroom](#yet-another-chatroom)
  - [Table of contents](#table-of-contents)
  - [Features](#features)
  - [Screenshots](#screenshots)
  - [Demo](#demo)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [TODO](#todo)
  - [License and credits](#license-and-credits)
  - [Contributing](#contributing)
  - [JM26.NET](#jm26net)

## Features

- 📨 Easy to use and maintain chatroom
- 📡 Real-time messaging
- ⚙️ Customizable settings
- 📜 60 second history mode
- 🛡️ Privacy friendly public chatroom
- 📱 Responsive design
- 🎨 Themes
- 🚫 Spam filter
- 🎉 Captcha
- :construction: More features to come

## Screenshots

![Screenshot](./src/assets/theme-thumbnail/dark/default.png)
![Screenshot](./src/assets/theme-thumbnail/light/flatly.png)
![Screenshot](./src/assets/theme-thumbnail/dark/sketchy.png)
For more screenshots, see the [theme-thumbnail](./src/assets/theme-thumbnail) folder.

## Demo

Will be available soon.

## Installation

**NOTE**: This project is under active development and is not yet ready for production use. Please use with caution.

1. Donwload the latest release from the [releases page](./releases)
2. Extract the files to your web server (subdirectory or root)
3. Open the chatroom in your web browser
4. Customize in config.php and enjoy! 🎉

**IMPORTANT**: Set the folder permissions of `./assets/inc/` to 770 to prevent unauthorized access to the database and other essential files.

## Configuration

The chatroom can be configured by editing the `config.php` file. The settings are explained in the file itself.

## TODO

- Image support?
- User mention
- Web socket?
- improve api security

## License and credits

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

The chatroom uses the following libraries:

- [Cheetahchat](https://github.com/hamidsamak/cheetahchat) by Hamid Samak
- [iconCaptcha](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP) by Fabian Wennink
- [Spam Filter](https://github.com/IQAndreas/php-spam-filter) by Andreas Gohr

## Contributing

Feel free to contribute to this project by creating a pull request. If you have any questions, feel free to open an issue.

## JM26.NET

This project is made by [JMcrafter26](https://jm26.net). Refer to the [TEST.JM26.NET](https://test.jm26.net) for more information and other projects.
